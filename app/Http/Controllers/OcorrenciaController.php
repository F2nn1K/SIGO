<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\OcorrenciaFrota;
use App\Models\Veiculo;
use Illuminate\Support\Facades\DB;

class OcorrenciaController extends Controller
{
    /**
     * Página inicial de Ocorrências da Frota.
     */
    public function index()
    {
        // Buscar veículos para o select (apenas utilizáveis)
        $veiculos = Veiculo::select('id', 'placa', 'marca', 'modelo')
            ->where('status', '!=', 'inativo')
            ->orderBy('placa')
            ->get();

        return view('frota.ocorrencias.index', compact('veiculos'));
    }

    /**
     * Recebe a submissão da ocorrência e persiste na tabela.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'veiculo_id' => ['required', 'exists:veiculos,id'],
            'data' => ['required', 'date'],
            'hora' => ['required'],
            'descricao' => ['required', 'string', 'min:5'],
            'sugestao' => ['nullable', 'string'],
            'fotos' => ['nullable', 'array', 'max:5'],
            // max em KB (50 * 1024)
            'fotos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
        ]);

        $user = Auth::user();

        // Preparar BLOBs em memória (até 5)
        $blobs = [null, null, null, null, null];
        $mimes = [null, null, null, null, null];
        if ($request->hasFile('fotos')) {
            $i = 0;
            foreach ($request->file('fotos') as $file) {
                if (!$file) { continue; }
                if ($i >= 5) { break; }
                $blobs[$i] = file_get_contents($file->getRealPath());
                $mimes[$i] = $file->getClientMimeType();
                $i++;
            }
        }

        // Persistir na tabela ocorrencias_frota
        OcorrenciaFrota::create([
            'veiculo_id' => $validated['veiculo_id'],
            'user_id' => $user ? $user->id : null,
            'motorista_nome' => $user ? $user->name : null,
            'data' => $validated['data'],
            'hora' => $validated['hora'],
            'descricao' => $validated['descricao'],
            'sugestao' => $validated['sugestao'] ?? null,
            'foto1' => $blobs[0], 'foto1_mime' => $mimes[0],
            'foto2' => $blobs[1], 'foto2_mime' => $mimes[1],
            'foto3' => $blobs[2], 'foto3_mime' => $mimes[2],
            'foto4' => $blobs[3], 'foto4_mime' => $mimes[3],
            'foto5' => $blobs[4], 'foto5_mime' => $mimes[4],
        ]);

        return redirect()
            ->route('frota.ocorrencias.index')
            ->with('success', 'Ocorrência registrada com sucesso.');
    }

    /**
     * Página do gestor de ocorrências - lista veículos com ocorrências.
     */
    public function gestor()
    {
        // Buscar todos os veículos
        $veiculos = Veiculo::select('id', 'placa', 'marca', 'modelo', 'ano', 'status')
            ->orderBy('placa')
            ->get();

        // Para cada veículo, verificar se há ocorrências recentes
        $veiculosComOcorrencias = $veiculos->map(function($veiculo) {
            // Buscar ocorrências deste veículo (últimos 30 dias) que não estejam resolvidas
            $base = OcorrenciaFrota::where('veiculo_id', $veiculo->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->where(function($q){
                    $q->whereNull('status')->orWhere('status', '!=', 'resolvido');
                })
                ->orderBy('created_at', 'desc');

            $ocorrencias = (clone $base)->limit(3)->get();

            $totalOcorrencias = (clone $base)->count();
            
            return [
                'veiculo' => $veiculo,
                'total_ocorrencias' => $totalOcorrencias,
                'ocorrencias_recentes' => $ocorrencias,
                'tem_ocorrencias' => $totalOcorrencias > 0,
            ];
        });

        return view('frota.ocorrencias.gestor', compact('veiculosComOcorrencias'));
    }

    /**
     * API: Detalhes de uma ocorrência (para modal)
     */
    public function showOccurrence(int $id)
    {
        $o = OcorrenciaFrota::findOrFail($id);
        return response()->json([
            'id' => $o->id,
            'veiculo_id' => $o->veiculo_id,
            'motorista' => $o->motorista_nome,
            'data' => optional($o->data)->format('Y-m-d'),
            'hora' => $o->hora,
            'descricao' => $o->descricao,
            'sugestao' => $o->sugestao,
            'status' => $o->status ?? 'novo',
            'created_at' => $o->created_at?->format('d/m/Y H:i'),
        ]);
    }

    /**
     * API: Atualizar status (em_andamento | resolvido)
     */
    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:em_andamento,resolvido']
        ]);
        $o = OcorrenciaFrota::findOrFail($id);
        $oldStatus = $o->status ?? 'novo';
        $o->status = $data['status'];
        $o->resolved_at = $data['status'] === 'resolvido' ? now() : null;
        $o->save();

        // Registrar histórico de status
        \App\Models\StatusOcorrencia::create([
            'ocorrencia_id' => $o->id,
            'user_id' => auth()->id(),
            'status_from' => $oldStatus,
            'status_to' => $data['status'],
            'observacao' => null,
        ]);

        // Se em andamento, opcionalmente marcar veículo como manutencao
        if ($o->veiculo_id && $data['status'] === 'em_andamento') {
            Veiculo::where('id', $o->veiculo_id)->update(['status' => 'manutencao']);
        }
        if ($o->veiculo_id && $data['status'] === 'resolvido') {
            // volta para ativo se estiver em manutencao
            Veiculo::where('id', $o->veiculo_id)->where('status', 'manutencao')->update(['status' => 'ativo']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * API: Histórico completo de ocorrências por veículo
     */
    public function historicoVeiculo(int $veiculoId)
    {
        // Buscar todas as ocorrências do veículo (sem limite de tempo)
        $todasOcorrencias = OcorrenciaFrota::where('veiculo_id', $veiculoId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($o) {
                return [
                    'id' => $o->id,
                    'motorista_nome' => $o->motorista_nome,
                    'data' => $o->data?->format('Y-m-d'),
                    'hora' => $o->hora,
                    'descricao' => $o->descricao,
                    'sugestao' => $o->sugestao,
                    'status' => $o->status ?? 'novo',
                    'created_at' => $o->created_at?->toISOString(),
                    'resolved_at' => $o->resolved_at?->toISOString(),
                ];
            });

        // Separar por status
        $pendentes = $todasOcorrencias->whereNotIn('status', ['resolvido'])->values();
        $resolvidas = $todasOcorrencias->where('status', 'resolvido')->values();

        return response()->json([
            'pendentes' => $pendentes,
            'resolvidas' => $resolvidas,
            'todas' => $todasOcorrencias->values(),
        ]);
    }

    /**
     * API: Lista metadados das fotos disponíveis para uma ocorrência.
     */
    public function fotos(int $id)
    {
        $o = OcorrenciaFrota::findOrFail($id);

        $fotos = [];
        for ($i = 1; $i <= 5; $i++) {
            $blobField = 'foto' . $i;
            $mimeField = 'foto' . $i . '_mime';
            if (!empty($o->$blobField) && !empty($o->$mimeField)) {
                $fotos[] = [
                    'idx' => $i,
                    'mime' => $o->$mimeField,
                    'url' => url("/frota/ocorrencias/api/{$o->id}/foto/{$i}"),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $fotos,
        ]);
    }

    /**
     * API: Retorna o binário de uma foto específica (inline) para visualização.
     */
    public function foto(int $id, int $idx)
    {
        if ($idx < 1 || $idx > 5) {
            abort(404);
        }

        $o = OcorrenciaFrota::findOrFail($id);
        $blobField = 'foto' . $idx;
        $mimeField = 'foto' . $idx . '_mime';

        $data = $o->$blobField;
        $mime = $o->$mimeField ?: 'application/octet-stream';

        if (empty($data)) {
            abort(404);
        }

        $filename = 'ocorrencia-' . $o->id . '-foto' . $idx . '.' . $this->guessExtensionFromMime($mime);

        return response($data, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=86400');
    }

    /**
     * Inferir extensão simples a partir do MIME.
     */
    private function guessExtensionFromMime(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        return $map[strtolower($mime)] ?? 'bin';
    }
}


