<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PedidoComprasController extends Controller
{
    /**
     * Exibe a página de solicitação de pedidos de compras
     *
     * @return \Illuminate\View\View
     */
    public function solicitacao()
    {
        return view('pedidos.solicitacao');
    }

    /**
     * Exibe a página de autorização de pedidos de compras
     *
     * @return \Illuminate\View\View
     */
    public function autorizacao()
    {
        return view('pedidos.autorizacao_home');
    }

    /**
     * View: autorizações pendentes
     */
    public function autorizacoesPendentesView()
    {
        return view('pedidos.autorizacao_pendentes');
    }

    /**
     * View: autorizações aprovadas
     */
    public function autorizacoesAprovadasView()
    {
        return view('pedidos.autorizacao_aprovadas');
    }

    /**
     * View: autorizações rejeitadas
     */
    public function autorizacoesRejeitadasView()
    {
        return view('pedidos.autorizacao_rejeitadas');
    }

    /** View: histórico e interações do próprio usuário */
    public function minhasInteracoesView()
    {
        return view('pedidos.minhas_interacoes');
    }

    /**
     * Armazena uma nova solicitação de pedido de compras
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $centroCustoId = $request->centro_custo_id;
            $prioridade = $request->prioridade;
            $observacao = $request->observacao;
            $produtos = $request->produtos;
            $usuarioId = auth()->id();
            
            // Gera um número de pedido único para este envio (mesmo número para todos os itens do envio)
            $numPedido = 'PED-' . now()->format('Ymd-His') . '-' . str_pad((string) $usuarioId, 3, '0', STR_PAD_LEFT);

            // Validações básicas
            if (!$centroCustoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Centro de custo é obrigatório'
                ], 400);
            }

            if (!$produtos || count($produtos) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adicione pelo menos um produto'
                ], 400);
            }

            // Prioridade permitida (sem "urgente")
            if (!in_array($prioridade, ['baixa', 'media', 'alta'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prioridade inválida'
                ], 422);
            }

            // Salvar cada produto como uma linha na tabela solicitacao (mesmo num_pedido)
            foreach ($produtos as $produto) {
                // Sanitização básica para prevenir SQL injection
                $produtoNome = strip_tags(trim($produto['nome'] ?? ''));
                $quantidade = (int) ($produto['quantidade'] ?? 0);
                
                // Pula produtos inválidos
                if (empty($produtoNome) || $quantidade <= 0) {
                    continue;
                }
                
                \DB::table('solicitacao')->insert([
                    'num_pedido' => $numPedido,
                    'usuario_id' => $usuarioId,
                    'centro_custo_id' => $centroCustoId,
                    'produto_nome' => $produtoNome,
                    'quantidade' => $quantidade,
                    'prioridade' => $prioridade,
                    'observacao' => strip_tags(trim($observacao ?? '')),
                    'data_solicitacao' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitação enviada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar solicitação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprova um pedido de compras
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function aprovar(Request $request, $id)
    {
        try {
            // Atualizar status, data e aprovador na solicitação individual
            \DB::table('solicitacao')
                ->where('id', $id)
                ->update([
                    'aprovacao' => 'aprovado',
                    'data_aprovacao' => now(),
                    'id_aprovador' => auth()->id(),
                ]);

            \DB::table('interacao')->insert([
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'aprovacao',
                'mensagem' => $request->input('observacoes') ?? null,
                'dados_extras' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido aprovado com sucesso!'
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeita um pedido de compras
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejeitar(Request $request, $id)
    {
        try {
            // Atualizar status, data e aprovador na solicitação individual
            \DB::table('solicitacao')
                ->where('id', $id)
                ->update([
                    'aprovacao' => 'rejeitado',
                    'data_aprovacao' => now(),
                    'id_aprovador' => auth()->id(),
                ]);

            \DB::table('interacao')->insert([
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'rejeicao',
                'mensagem' => $request->input('observacoes') ?? null,
                'dados_extras' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido rejeitado com sucesso!'
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao rejeitar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista as solicitações do usuário logado
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function minhasSolicitacoes()
    {
        $usuarioId = auth()->id();

        // Última interação (aprovacao/rejeicao) por solicitacao
        $ultimasInteracoes = \DB::table('interacao')
            ->select('solicitacao_id', \DB::raw('MAX(created_at) as max_created'))
            ->whereIn('tipo', ['aprovacao', 'rejeicao'])
            ->groupBy('solicitacao_id');

        $statusPorInteracao = \DB::table('interacao as i')
            ->joinSub($ultimasInteracoes, 'ui', function ($join) {
                $join->on('i.solicitacao_id', '=', 'ui.solicitacao_id')
                     ->on('i.created_at', '=', 'ui.max_created');
            })
            ->select('i.solicitacao_id', 'i.tipo');

        $registros = \DB::table('solicitacao as s')
            ->leftJoinSub($statusPorInteracao, 'st', function ($join) {
                $join->on('s.id', '=', 'st.solicitacao_id');
            })
            ->where('s.usuario_id', $usuarioId)
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.*',
                \DB::raw("COALESCE(st.tipo, 'pendente') as status")
            ]);

        return response()->json([
            'success' => true,
            'data' => $registros
        ]);
    }

    /** Dados para a página de interações: pedidos apenas do usuário logado, agrupados por num_pedido */
    public function minhasInteracoesData()
    {
        $usuarioId = auth()->id();

        $pedidos = \DB::table('solicitacao as s')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.usuario_id', $usuarioId)
            ->where('s.aprovacao', '=', 'pendente')
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.id', 's.num_pedido', 's.produto_nome', 's.quantidade', 's.prioridade',
                's.aprovacao', 's.observacao', 's.data_solicitacao', 's.data_aprovacao',
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome")
            ]);

        // Agrupar por num_pedido
        $agrupado = $pedidos->groupBy('num_pedido')->map(function($items){
            return [
                'num_pedido' => $items->first()->num_pedido,
                'data_solicitacao' => $items->first()->data_solicitacao,
                'centro_custo_nome' => $items->first()->centro_custo_nome,
                'prioridade' => $items->first()->prioridade,
                'aprovacao' => 'pendente',
                'itens' => $items->map(function($i){ return [
                    'id' => $i->id,
                    'produto_nome' => $i->produto_nome,
                    'quantidade' => $i->quantidade,
                ]; })->values(),
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $agrupado]);
    }

    /** Interações (aprovação/rejeição/mensagens) por item de solicitação */
    public function interacoesPorPedido($id)
    {
        $usuarioId = auth()->id();
        // Garantir que o pedido pertence ao usuário
        $pertence = \DB::table('solicitacao')->where('id', $id)->where('usuario_id', $usuarioId)->exists();
        if (!$pertence) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $interacoes = \DB::table('interacao as i')
            ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
            ->where('i.solicitacao_id', $id)
            ->orderByDesc('i.created_at')
            ->get([
                'i.id', 'i.tipo', 'i.mensagem', 'i.created_at', \DB::raw("COALESCE(u.name,'—') as usuario")
            ]);

        return response()->json(['success' => true, 'data' => $interacoes]);
    }

    /** Registrar uma mensagem do solicitante no pedido (apenas pendente) */
    public function enviarInteracaoSolicitante(Request $request, $id)
    {
        $request->validate([
            'mensagem' => 'required|string|min:2|max:2000',
        ]);

        $usuarioId = auth()->id();
        $pedido = \DB::table('solicitacao')->where('id', $id)->first();
        if (!$pedido || (int)$pedido->usuario_id !== (int)$usuarioId) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }
        if ($pedido->aprovacao !== 'pendente') {
            return response()->json(['success' => false, 'message' => 'Pedido não está mais pendente'], 400);
        }

        \DB::table('interacao')->insert([
            'solicitacao_id' => $id,
            'usuario_id' => $usuarioId,
            'tipo' => 'comentario',
            'mensagem' => $request->input('mensagem'),
            'dados_extras' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Mensagem enviada']);
    }

    /**
     * Lista pedidos pendentes de autorização
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pedidosPendentes()
    {
        $pendentes = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'pendente')
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.*',
                \DB::raw("COALESCE(u.name, '—') as solicitante"),
                \DB::raw("COALESCE(cc.nome, '—') as centro_custo_nome"),
            ]);

        return response()->json([
            'success' => true,
            'data' => $pendentes
        ]);
    }

    /**
     * Lista pendentes agrupados por envio (mesmos dados e mesma data de envio)
     */
    public function pedidosPendentesAgrupados()
    {
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $grupos = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'pendente')
            ->groupByRaw("$hashExpr, s.usuario_id, s.centro_custo_id, s.prioridade, COALESCE(s.observacao,''), DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s'), u.name, cc.nome")
            ->orderByDesc('data_solicitacao')
            ->get([
                \DB::raw("$hashExpr as grupo_hash"),
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.prioridade',
                \DB::raw("COALESCE(s.observacao,'') as observacao"),
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw('MIN(s.num_pedido) as num_pedido'),
                \DB::raw('COUNT(*) as itens'),
                \DB::raw('SUM(s.quantidade) as quantidade_total'),
            ]);

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /**
     * Detalhes de um grupo pendente (cabeçalho + itens)
     */
    public function detalhesPedidoAgrupado(string $hash)
    {
        // Validação do hash para prevenir SQL injection
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            return response()->json(['success' => false, 'message' => 'Hash inválido'], 400);
        }
        
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $cabecalho = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderByDesc('s.data_solicitacao')
            ->first([
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.num_pedido',
                's.prioridade',
                's.observacao',
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
            ]);

        if (!$cabecalho) {
            return response()->json(['success' => false, 'message' => 'Grupo não encontrado'], 404);
        }

        $itens = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderBy('s.id')
            ->get([
                's.id',
                's.produto_nome',
                's.quantidade',
            ]);

        // Interações de todos os itens do grupo
        $idsGrupo = $itens->pluck('id')->all();
        $interacoes = [];
        if (!empty($idsGrupo)) {
            $interacoes = \DB::table('interacao as i')
                ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
                ->whereIn('i.solicitacao_id', $idsGrupo)
                ->orderByDesc('i.created_at')
                ->get([
                    'i.id', 'i.solicitacao_id', 'i.tipo', 'i.mensagem', 'i.created_at',
                    \DB::raw("COALESCE(u.name,'—') as usuario")
                ]);
        }

        return response()->json(['success' => true, 'data' => [
            'cabecalho' => $cabecalho,
            'itens' => $itens,
            'interacoes' => $interacoes,
        ]]);
    }

    /** Aprova todas as solicitações do grupo */
    public function aprovarGrupo(Request $request, string $hash)
    {
        // Validação do hash para prevenir SQL injection
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            return response()->json(['success' => false, 'message' => 'Hash inválido'], 400);
        }
        
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $ids = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->pluck('s.id');

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Grupo não encontrado'], 404);
        }

        $mensagem = $request->input('mensagem');
        $agora = now();

        $inserts = $ids->map(function ($id) use ($mensagem, $agora) {
            return [
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'aprovacao',
                'mensagem' => $mensagem,
                'dados_extras' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        })->all();

        // Atualiza status, data e aprovador do grupo
        \DB::table('solicitacao')->whereIn('id', $ids)->update([
            'aprovacao' => 'aprovado',
            'data_aprovacao' => $agora,
            'id_aprovador' => auth()->id(),
        ]);

        // Registro de interação (opcional)
        if (!empty($mensagem)) {
            \DB::table('interacao')->insert($inserts);
        }

        return response()->json(['success' => true, 'message' => 'Pedido aprovado com sucesso']);
    }

    /** Rejeita todas as solicitações do grupo */
    public function rejeitarGrupo(Request $request, string $hash)
    {
        // Validação do hash para prevenir SQL injection
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            return response()->json(['success' => false, 'message' => 'Hash inválido'], 400);
        }
        
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $ids = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->pluck('s.id');

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Grupo não encontrado'], 404);
        }

        $mensagem = $request->input('mensagem');
        $agora = now();

        $inserts = $ids->map(function ($id) use ($mensagem, $agora) {
            return [
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'rejeicao',
                'mensagem' => $mensagem,
                'dados_extras' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        })->all();

        // Atualiza status, data e aprovador do grupo
        \DB::table('solicitacao')->whereIn('id', $ids)->update([
            'aprovacao' => 'rejeitado',
            'data_aprovacao' => $agora,
            'id_aprovador' => auth()->id(),
        ]);

        // Registro de interação (opcional)
        if (!empty($mensagem)) {
            \DB::table('interacao')->insert($inserts);
        }

        return response()->json(['success' => true, 'message' => 'Pedido rejeitado com sucesso']);
    }

    /** Enviar mensagem do autorizador para o solicitante em um grupo */
    public function mensagemGrupo(Request $request, string $hash)
    {
        // Validação do hash para prevenir SQL injection
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            return response()->json(['success' => false, 'message' => 'Hash inválido'], 400);
        }
        
        $request->validate(['mensagem' => 'required|string|min:2|max:2000']);

        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";
        $ids = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->pluck('s.id');

        if ($ids->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Grupo não encontrado'], 404);
        }

        $agora = now();
        $mensagem = $request->input('mensagem');
        $inserts = $ids->map(function ($id) use ($mensagem, $agora) {
            return [
                'solicitacao_id' => $id,
                'usuario_id' => auth()->id(),
                'tipo' => 'comentario',
                'mensagem' => $mensagem,
                'dados_extras' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        })->all();

        \DB::table('interacao')->insert($inserts);

        return response()->json(['success' => true, 'message' => 'Mensagem enviada']);
    }

    /**
     * Lista pedidos aprovados (última interação = aprovacao)
     */
    public function pedidosAprovados(Request $request)
    {
        $aprovadosQuery = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'aprovado')
            ->orderByDesc('s.data_solicitacao');

        // filtros de data (data_solicitacao)
        if ($request->filled('data_ini')) {
            $aprovadosQuery->whereDate('s.data_solicitacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $aprovadosQuery->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
        }

        $aprovados = $aprovadosQuery->get([
                's.*',
                \DB::raw("COALESCE(u.name, '—') as solicitante"),
                \DB::raw("COALESCE(cc.nome, '—') as centro_custo_nome"),
                's.data_aprovacao as data_decisao',
            ]);

        return response()->json(['success' => true, 'data' => $aprovados]);
    }

    /**
     * Lista pedidos rejeitados (última interação = rejeicao)
     */
    public function pedidosRejeitados(Request $request)
    {
        $rejeitadosQuery = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'rejeitado')
            ->orderByDesc('s.data_solicitacao');

        if ($request->filled('data_ini')) {
            $rejeitadosQuery->whereDate('s.data_solicitacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $rejeitadosQuery->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
        }

        $rejeitados = $rejeitadosQuery->get([
                's.*',
                \DB::raw("COALESCE(u.name, '—') as solicitante"),
                \DB::raw("COALESCE(cc.nome, '—') as centro_custo_nome"),
                's.data_aprovacao as data_decisao',
            ]);

        return response()->json(['success' => true, 'data' => $rejeitados]);
    }

    /**
     * Aprovados agrupados por envio
     */
    public function pedidosAprovadosAgrupados(Request $request)
    {
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $query = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'aprovado')
            ->groupByRaw("$hashExpr, s.usuario_id, s.centro_custo_id, s.prioridade, COALESCE(s.observacao,''), DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s'), u.name, cc.nome")
            ->orderByDesc('data_solicitacao')
            ;

        if ($request->filled('data_ini')) {
            $query->whereDate('s.data_solicitacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
        }

        $grupos = $query->get([
                \DB::raw("$hashExpr as grupo_hash"),
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.prioridade',
                \DB::raw("COALESCE(s.observacao,'') as observacao"),
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw('MIN(s.num_pedido) as num_pedido'),
                \DB::raw('COUNT(*) as itens'),
                \DB::raw('SUM(s.quantidade) as quantidade_total'),
            ]);

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /**
     * Rejeitados agrupados por envio
     */
    public function pedidosRejeitadosAgrupados(Request $request)
    {
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $query = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.aprovacao', '=', 'rejeitado')
            ->groupByRaw("$hashExpr, s.usuario_id, s.centro_custo_id, s.prioridade, COALESCE(s.observacao,''), DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s'), u.name, cc.nome")
            ->orderByDesc('data_solicitacao')
            ;

        if ($request->filled('data_ini')) {
            $query->whereDate('s.data_solicitacao', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('s.data_solicitacao', '<=', $request->input('data_fim'));
        }

        $grupos = $query->get([
                \DB::raw("$hashExpr as grupo_hash"),
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.prioridade',
                \DB::raw("COALESCE(s.observacao,'') as observacao"),
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                \DB::raw('MIN(s.num_pedido) as num_pedido'),
                \DB::raw('COUNT(*) as itens'),
                \DB::raw('SUM(s.quantidade) as quantidade_total'),
            ]);

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /**
     * Busca produtos por nome (autocomplete)
     * Lista de produtos sugeridos para autocomplete
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarProdutos(Request $request)
    {
        $termo = $request->get('termo');
        
        // Sanitização para prevenir SQL injection
        $termo = preg_replace('/[^a-zA-Z0-9\sÀ-ÿ\-]/', '', $termo);
        
        if (strlen($termo) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Digite ao menos 3 caracteres'
            ]);
        }

        try {
            // Ajustado para a estrutura real: estoque_pedido(produto, descricao, centro_custo_id)
            $produtos = \DB::table('estoque_pedido as ep')
                ->leftJoin('centro_custo as cc', 'cc.id', '=', 'ep.centro_custo_id')
                ->where('ep.produto', 'LIKE', "%{$termo}%")
                ->orderBy('ep.produto')
                ->limit(10)
                ->get([
                    'ep.id',
                    \DB::raw('ep.produto as nome'),
                    'ep.descricao',
                    'ep.centro_custo_id',
                    \DB::raw('COALESCE(cc.nome, "") as centro_custo_nome'),
                ]);

            return response()->json([
                'success' => true,
                'data' => $produtos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Lista centros de custo (todos)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarCentrosCusto()
    {
        try {
            $centrosCusto = \DB::table('centro_custo')
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome']);

            return response()->json([
                'success' => true,
                'data' => $centrosCusto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar centros de custo'
            ]);
        }
    }

    /**
     * Busca centros de custo por nome (autocomplete)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarCentrosCustoAutocomplete(Request $request)
    {
        $termo = $request->get('termo');
        
        if (strlen($termo) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Digite ao menos 3 caracteres'
            ]);
        }

        try {
            $centrosCusto = \DB::table('centro_custo')
                ->where('ativo', true)
                ->where('nome', 'LIKE', "%{$termo}%")
                ->orderBy('nome')
                ->limit(10)
                ->get(['id', 'nome']);

            return response()->json([
                'success' => true,
                'data' => $centrosCusto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar centros de custo'
            ]);
        }
    }

    /** View: Acompanhar Pedido (somente leitura) */
    public function acompanharView()
    {
        return view('pedidos.acompanhar_home');
    }

    /** Lista agrupada (pendente/aprovado/rejeitado) só do usuário logado */
    public function acompanharLista()
    {
        $usuarioId = auth()->id();

        // Carrega os registros do usuário e realiza o agrupamento em PHP para evitar
        // problemas com modos SQL (ONLY_FULL_GROUP_BY) e diferenças de collation.
        $registros = \DB::table('solicitacao as s')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.usuario_id', $usuarioId)
            ->orderByDesc('s.data_solicitacao')
            ->get([
                's.id', 's.usuario_id', 's.centro_custo_id', 's.num_pedido', 's.prioridade',
                's.observacao', 's.data_solicitacao', 's.quantidade', 's.aprovacao',
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
            ]);

        $grupos = $registros->groupBy(function ($r) {
            $data = \Carbon\Carbon::parse($r->data_solicitacao)->format('Y-m-d H:i:s');
            return sha1($r->usuario_id . '|' . $r->centro_custo_id . '|' . $r->prioridade . '|' . ($r->observacao ?? '') . '|' . $data);
        })->map(function ($items) {
            $first = $items->first();

            // Determina o status do grupo: se algum item estiver rejeitado, prioriza rejeitado;
            // senão, se algum estiver aprovado, é aprovado; caso contrário, pendente.
            $status = 'pendente';
            if ($items->contains(function ($i) { return $i->aprovacao === 'rejeitado'; })) {
                $status = 'rejeitado';
            } elseif ($items->contains(function ($i) { return $i->aprovacao === 'aprovado'; })) {
                $status = 'aprovado';
            }

            $dataFormatada = \Carbon\Carbon::parse($first->data_solicitacao)->format('Y-m-d H:i:s');
            $grupoHash = sha1($first->usuario_id . '|' . $first->centro_custo_id . '|' . $first->prioridade . '|' . ($first->observacao ?? '') . '|' . $dataFormatada);

            return (object) [
                'grupo_hash' => $grupoHash,
                'num_pedido' => $first->num_pedido,
                'data_solicitacao' => $dataFormatada,
                'centro_custo_nome' => $first->centro_custo_nome,
                'itens' => $items->count(),
                'quantidade_total' => $items->sum('quantidade'),
                'prioridade' => $first->prioridade,
                'status' => $status,
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $grupos]);
    }

    /** Detalhes só leitura do grupo do usuário */
    public function acompanharDetalhes(string $hash)
    {
        $usuarioId = auth()->id();
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $cabecalho = \DB::table('solicitacao as s')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->where('s.usuario_id', $usuarioId)
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderByDesc('s.data_solicitacao')
            ->first([
                's.num_pedido',
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
                's.prioridade', 's.aprovacao'
            ]);

        if (!$cabecalho) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado'], 404);
        }

        $itens = \DB::table('solicitacao as s')
            ->where('s.usuario_id', $usuarioId)
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderBy('s.id')
            ->get(['s.id','s.produto_nome','s.quantidade']);

        $interacoes = \DB::table('interacao as i')
            ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
            ->whereIn('i.solicitacao_id', $itens->pluck('id')->all())
            ->orderByDesc('i.created_at')
            ->get(['i.id','i.tipo','i.mensagem','i.created_at', \DB::raw("COALESCE(u.name,'—') as usuario")]);

        return response()->json(['success' => true, 'data' => compact('cabecalho','itens','interacoes')]);
    }

    /**
     * Gera layout de impressão para um pedido específico
     */
    public function imprimirPedido(string $hash)
    {
        // Validação do hash para prevenir SQL injection
        if (!preg_match('/^[a-f0-9]{40}$/', $hash)) {
            abort(404, 'Hash inválido');
        }
        
        $hashExpr = "SHA1(CONCAT(s.usuario_id,'|',s.centro_custo_id,'|',s.prioridade,'|',COALESCE(s.observacao,''),'|', DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s')))";

        $cabecalho = \DB::table('solicitacao as s')
            ->leftJoin('users as u', 'u.id', '=', 's.usuario_id')
            ->leftJoin('centro_custo as cc', 'cc.id', '=', 's.centro_custo_id')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderByDesc('s.data_solicitacao')
            ->first([
                \DB::raw("DATE_FORMAT(s.data_solicitacao,'%Y-%m-%d %H:%i:%s') as data_solicitacao"),
                's.usuario_id',
                's.centro_custo_id',
                's.num_pedido',
                's.prioridade',
                's.observacao',
                \DB::raw("COALESCE(u.name,'—') as solicitante"),
                \DB::raw("COALESCE(cc.nome,'—') as centro_custo_nome"),
            ]);

        if (!$cabecalho) {
            abort(404, 'Pedido não encontrado');
        }

        $itens = \DB::table('solicitacao as s')
            ->whereRaw("$hashExpr = ?", [$hash])
            ->orderBy('s.id')
            ->get([
                's.id',
                's.produto_nome',
                's.quantidade',
            ]);

        // Interações de todos os itens do grupo
        $idsGrupo = $itens->pluck('id')->all();
        $interacoes = [];
        if (!empty($idsGrupo)) {
            $interacoes = \DB::table('interacao as i')
                ->leftJoin('users as u', 'u.id', '=', 'i.usuario_id')
                ->whereIn('i.solicitacao_id', $idsGrupo)
                ->orderByDesc('i.created_at')
                ->get([
                    'i.id', 'i.solicitacao_id', 'i.tipo', 'i.mensagem', 'i.created_at',
                    \DB::raw("COALESCE(u.name,'—') as usuario")
                ]);
        }

        return view('relatorios.imprimir-pedido', compact('cabecalho', 'itens', 'interacoes'));
    }

    
}