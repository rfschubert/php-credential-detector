<?php

namespace RfSchubert\CredentialDetector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RfSchubert\CredentialDetector\Exception\ModelNotFoundException;
use RfSchubert\CredentialDetector\Services\OnnxModelService;

class OnnxModelServiceTest extends TestCase
{
    /**
     * Testa que uma exceção é lançada quando o modelo não é encontrado
     */
    public function testExceptionWhenModelNotFound()
    {
        $service = new OnnxModelService('/caminho/inexistente/modelo.onnx');
        
        $this->expectException(ModelNotFoundException::class);
        $service->loadModel();
    }
    
    /**
     * Testa a predição com simulação
     */
    public function testPredictionWithKeywords()
    {
        // Criar um arquivo temporário para simular o modelo
        $tempFile = tempnam(sys_get_temp_dir(), 'onnx_');
        file_put_contents($tempFile, 'TESTE');
        
        $service = new OnnxModelService($tempFile);
        
        // Testar a predição com texto que contém palavras-chave
        list($confidence, $isCredential) = $service->predict('API_KEY=123');
        
        $this->assertGreaterThan(0.5, $confidence);
        $this->assertTrue($isCredential);
        
        // Limpar o arquivo temporário
        unlink($tempFile);
    }
    
    /**
     * Testa o comportamento com texto comum
     */
    public function testPredictionWithNormalText()
    {
        // Criar um arquivo temporário para simular o modelo
        $tempFile = tempnam(sys_get_temp_dir(), 'onnx_');
        file_put_contents($tempFile, 'TESTE');
        
        $service = new OnnxModelService($tempFile);
        
        // Testar a predição com texto normal
        list($confidence, $isCredential) = $service->predict('Este é um texto comum sem credenciais');
        
        $this->assertLessThan(0.5, $confidence);
        $this->assertFalse($isCredential);
        
        // Limpar o arquivo temporário
        unlink($tempFile);
    }
    
    /**
     * Testa o pré-processamento do texto
     */
    public function testTextPreprocessing()
    {
        $service = new OnnxModelService('/caminho/simulado/modelo.onnx');
        
        // Acessar o método protegido de pré-processamento
        $reflector = new \ReflectionClass($service);
        $method = $reflector->getMethod('preprocessText');
        $method->setAccessible(true);
        
        $result = $method->invoke($service, 'API_KEY');
        
        // Verifica se o resultado é um array de caracteres
        $this->assertEquals(['A', 'P', 'I', '_', 'K', 'E', 'Y'], $result);
    }
} 