<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissoesController extends Controller
{
    /**
     * Exibe a página de permissões - SIMPLES: só puxa dados do banco
     */
    public function index()
    {
        $permissoes = DB::table('permissions')->get();
        return view('admin.gerenciar-permissoes-sistema', compact('permissoes'));
    }
    
    /**
     * Obtém uma permissão específica
     */
    public function show($id)
    {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $permissao
        ]);
    }
    
    /**
     * Cria uma nova permissão
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'code' => 'nullable|string|max:255|unique:permissions,code',
            'description' => 'nullable|string'
        ]);
        
        $id = DB::table('permissions')->insertGetId([
            'name' => $request->name,
            'code' => $request->code ?? strtolower(str_replace(' ', '_', $request->name)),
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso',
            'data' => $permissao
        ], 201);
    }
    
    /**
     * Atualiza uma permissão
     */
    public function update(Request $request, $id)
    {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'code' => 'nullable|string|max:255|unique:permissions,code,' . $id,
            'description' => 'nullable|string'
        ]);
        
        DB::table('permissions')->where('id', $id)->update([
            'name' => $request->name,
            'code' => $request->code ?? $permissao->code,
            'description' => $request->description,
            'updated_at' => now()
        ]);
        
        $permissaoAtualizada = DB::table('permissions')->where('id', $id)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso',
            'data' => $permissaoAtualizada
        ]);
    }
    
    /**
     * Exclui uma permissão
     */
    public function destroy($id)
    {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        // Remover relacionamentos primeiro
        DB::table('profile_permissions')->where('permission_id', $id)->delete();
        
        // Remover permissão
        DB::table('permissions')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso'
        ]);
    }
} 