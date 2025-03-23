<?php

// Este é um exemplo de como usar a biblioteca em um controlador Laravel

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RfSchubert\CredentialDetector\Laravel\Facades\CredentialDetector;

class ExemploController extends Controller
{
    /**
     * Verifica se um texto contém credenciais
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarCredenciais(Request $request)
    {
        // Validar a entrada
        $request->validate([
            'texto' => 'required|string'
        ]);

        // Verificar se contém credenciais
        $texto = $request->input('texto');
        $resultado = CredentialDetector::detect($texto);

        // Preparar resposta
        $resposta = [
            'texto' => $texto,
            'tem_credencial' => $resultado->hasCredential(),
            'confianca' => $resultado->getConfidence(),
            'correspondencias' => $resultado->getMatches()
        ];

        return response()->json($resposta);
    }

    /**
     * Exemplo de middleware de segurança para prevenir envio de credenciais
     * Esta é uma simulação de como usar o detector em um middleware Laravel
     */
    public function exemploMiddleware($request, $next)
    {
        // Verificar conteúdo da mensagem
        $conteudo = $request->input('mensagem');
        
        if (!empty($conteudo)) {
            $resultado = CredentialDetector::detect($conteudo);
            
            if ($resultado->hasCredential()) {
                // Conteúdo contém credenciais - bloquear ou mascarar
                return response()->json([
                    'erro' => 'Mensagem bloqueada',
                    'motivo' => 'A mensagem parece conter informações sensíveis ou credenciais.'
                ], 403);
            }
        }
        
        return $next($request);
    }
} 