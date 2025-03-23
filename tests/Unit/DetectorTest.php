<?php

namespace RfSchubert\CredentialDetector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RfSchubert\CredentialDetector\Detector;
use RfSchubert\CredentialDetector\Services\OnnxModelService;
use RfSchubert\CredentialDetector\Services\RegexMatcher;
use RfSchubert\CredentialDetector\Models\DetectionResult;

class DetectorTest extends TestCase
{
    /**
     * Testa a detecção de credenciais simples usando regex
     */
    public function testDetectsSimpleCredentialWithRegex()
    {
        $detector = $this->createMockDetector();
        
        $result = $detector->detect('api_key = "abc123def456ghi789jkl012"');
        
        $this->assertTrue($result->hasCredential());
        $this->assertGreaterThanOrEqual(0.7, $result->getConfidence());
        $this->assertCount(1, $result->getMatches());
    }
    
    /**
     * Testa que textos normais não são detectados como credenciais
     */
    public function testNoCredentialInNormalText()
    {
        $detector = $this->createMockDetector();
        
        $result = $detector->detect('Este é um texto normal sem credenciais.');
        
        $this->assertFalse($result->hasCredential());
        $this->assertLessThan(0.7, $result->getConfidence());
        $this->assertCount(0, $result->getMatches());
    }
    
    /**
     * Testa que a integração entre IA e Regex escolhe o resultado mais confiável
     */
    public function testAiAndRegexIntegration()
    {
        $detector = $this->createMockDetector(0.7, true, 0.8);
        
        $result = $detector->detect('Um texto que parece conter uma api_key');
        
        $this->assertTrue($result->hasCredential());
        $this->assertGreaterThanOrEqual(0.8, $result->getConfidence());
    }
    
    /**
     * Testa o comportamento quando a IA detecta mas o regex não
     */
    public function testAiDetectionWhenRegexFails()
    {
        $detector = $this->createMockDetector(0.7, false, 0.85);
        
        $result = $detector->detect('Um texto que contém uma credencial não padrão');
        
        $this->assertTrue($result->hasCredential());
        $this->assertGreaterThanOrEqual(0.85, $result->getConfidence());
        $this->assertCount(1, $result->getMatches());
        
        // Verifica se a posição do match é o texto completo
        $positions = $result->getMatchPositions();
        $this->assertEquals(0, $positions[0][0]); // início
        $this->assertEquals(47, $positions[0][1]); // fim (comprimento do texto)
        $this->assertEquals('ai_detected', $positions[0][2]); // tipo
    }
    
    /**
     * Cria um detector mock para testes
     */
    private function createMockDetector(float $threshold = 0.7, bool $regexMatches = true, float $aiConfidence = 0.0): Detector
    {
        // Não queremos que o detector baixe ou carregue o modelo real durante os testes
        $detector = $this->getMockBuilder(Detector::class)
            ->setConstructorArgs([$threshold, null, false])
            ->onlyMethods(['ensureModelAvailable', 'loadOnnxModel'])
            ->getMock();
        
        // Mock para o RegexMatcher
        $regexMatcher = $this->createMock(RegexMatcher::class);
        
        // Configurar o comportamento do RegexMatcher
        $matches = $regexMatches ? ['api_key123'] : [];
        $positions = $regexMatches ? [[0, 10, 'api_key']] : [];
        
        $regexMatcher->method('findMatches')
            ->willReturn([$matches, $positions]);
        
        // Substituir o RegexMatcher no detector
        $reflector = new \ReflectionClass($detector);
        $property = $reflector->getProperty('regexMatcher');
        $property->setAccessible(true);
        $property->setValue($detector, $regexMatcher);
        
        // Mock para o OnnxModelService se houver valor de confiança da IA
        if ($aiConfidence > 0) {
            $onnxService = $this->createMock(OnnxModelService::class);
            
            $onnxService->method('predict')
                ->willReturn([$aiConfidence, $aiConfidence >= 0.5]);
            
            $onnxProperty = $reflector->getProperty('onnxService');
            $onnxProperty->setAccessible(true);
            $onnxProperty->setValue($detector, $onnxService);
        }
        
        return $detector;
    }
} 