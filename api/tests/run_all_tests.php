<?php
/**
 * Script para ejecutar todas las pruebas
 * Ejecutar desde línea de comandos: php run_all_tests.php
 */

require_once __DIR__ . '/config.php';

class TestRunner {
    private $startTime;
    private $testResults = [
        'total' => 0,
        'passed' => 0,
        'failed' => 0
    ];

    public function run() {
        $this->startTime = microtime(true);
        
        echo "\n🚀 Iniciando suite de pruebas AVI API\n";
        echo "=====================================\n";

        // 1. Ejecutar pruebas de usuarios
        $this->runTest('Pruebas de Usuarios', 'test_usuarios.php');

        // 2. Ejecutar pruebas de ideas
        $this->runTest('Pruebas de Ideas', 'test_ideas.php');

        // 3. Ejecutar pruebas de notificaciones
        $this->runTest('Pruebas de Notificaciones', 'test_notificaciones.php');

        $this->showSummary();
    }

    private function runTest($testName, $testFile) {
        echo "\n📋 Ejecutando $testName...\n";
        
        $this->testResults['total']++;
        
        try {
            // Capturar la salida del script
            ob_start();
            $result = include __DIR__ . '/' . $testFile;
            $output = ob_get_clean();

            // Si no hay excepciones y el resultado no es false, la prueba pasó
            if ($result !== false) {
                $this->testResults['passed']++;
                echo "✅ $testName completadas exitosamente\n";
                if (VERBOSE_OUTPUT) {
                    echo $output;
                }
            } else {
                $this->testResults['failed']++;
                echo "❌ $testName fallidas\n";
                echo $output;
            }

        } catch (Exception $e) {
            $this->testResults['failed']++;
            echo "❌ Error en $testName: " . $e->getMessage() . "\n";
        }
    }

    private function showSummary() {
        $endTime = microtime(true);
        $executionTime = round($endTime - $this->startTime, 2);

        echo "\n📊 Resumen de Pruebas\n";
        echo "===================\n";
        echo "Total pruebas: {$this->testResults['total']}\n";
        echo "✅ Exitosas: {$this->testResults['passed']}\n";
        echo "❌ Fallidas: {$this->testResults['failed']}\n";
        echo "⏱️ Tiempo de ejecución: {$executionTime} segundos\n";

        // Si todas las pruebas pasaron
        if ($this->testResults['failed'] === 0) {
            echo "\n🎉 ¡Todas las pruebas pasaron exitosamente!\n";
        } else {
            echo "\n⚠️ Algunas pruebas fallaron. Revisa los detalles anteriores.\n";
        }
    }
}

// Ejecutar todas las pruebas
try {
    $runner = new TestRunner();
    $runner->run();
} catch (Exception $e) {
    echo "\n❌ Error fatal durante la ejecución de pruebas: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Instrucciones de uso:
 * 
 * 1. Asegúrate de que la API esté corriendo y accesible
 * 2. Configura la URL base en config.php
 * 3. Ejecuta desde la línea de comandos:
 *    php run_all_tests.php
 * 
 * Para ejecutar pruebas específicas:
 * - php test_usuarios.php
 * - php test_ideas.php
 * - php test_notificaciones.php
 */
