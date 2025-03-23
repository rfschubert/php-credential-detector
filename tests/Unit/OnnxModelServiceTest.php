<?php

namespace RfSchubert\CredentialDetector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RfSchubert\CredentialDetector\Exception\ModelNotFoundException;
use RfSchubert\CredentialDetector\Services\OnnxModelService;
use PhpML\ONNX\Model;
use PhpML\ONNX\Exception\RuntimeException;

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
     * Testa a predição com modelo simulado
     */
    public function testPredictionWithMockModel()
    {
        // Criar um serviço com mock
        $service = $this->getMockBuilder(OnnxModelService::class)
            ->setConstructorArgs(['/caminho/simulado/modelo.onnx'])
            ->onlyMethods(['loadModel'])
            ->getMock();
        
        // Substituir o modelo interno por um mock
        $mockModel = $this->createMock(Model::class);
        $mockModel->method('predict')
            ->willReturn(['output' => [[0.1, 0.9]]]);
        
        // Injetar o mock do modelo
        $reflector = new \ReflectionClass($service);
        $property = $reflector->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($service, $mockModel);
        
        // Testar a predição
        list($confidence, $isCredential) = $service->predict('API_KEY=123');
        
        $this->assertEquals(0.9, $confidence);
        $this->assertTrue($isCredential);
    }
    
    /**
     * Testa o comportamento quando ocorre um erro na predição
     */
    public function testHandlesExceptionDuringPrediction()
    {
        // Criar um serviço com mock
        $service = $this->getMockBuilder(OnnxModelService::class)
            ->setConstructorArgs(['/caminho/simulado/modelo.onnx'])
            ->onlyMethods(['loadModel'])
            ->getMock();
        
        // Substituir o modelo interno por um mock que lança exceção
        $mockModel = $this->createMock(Model::class);
        $mockModel->method('predict')
            ->will($this->throwException(new \Exception('Erro de simulação')));
        
        // Injetar o mock do modelo
        $reflector = new \ReflectionClass($service);
        $property = $reflector->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($service, $mockModel);
        
        // A predição deve retornar [0, false] quando ocorre erro
        list($confidence, $isCredential) = $service->predict('API_KEY=123');
        
        $this->assertEquals(0, $confidence);
        $this->assertFalse($isCredential);
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