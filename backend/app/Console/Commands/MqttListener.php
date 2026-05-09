<?php

namespace App\Console\Commands;

use App\Models\SensorData;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;

class MqttListener extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT topics and store sensor data';

    public function handle()
    {
        $this->info('Starting MQTT Listener...');
        
        $mqtt = new MqttClient('localhost', 1883, 'qandang-backend-listener');
        
        $mqtt->connect();
        
        $mqtt->subscribe('qandang/barn/sensors', function (string $topic, string $message) {
            $this->info("Received message on topic [$topic]: $message");
            
            $data = json_decode($message, true);
            
            if (isset($data['temperature']) && isset($data['humidity'])) {
                SensorData::create([
                    'temperature' => $data['temperature'],
                    'humidity' => $data['humidity'],
                ]);
                $this->info('Data stored successfully.');
            } else {
                $this->error('Invalid data format.');
            }
        }, 0);
        
        $mqtt->loop(true);
    }
}
