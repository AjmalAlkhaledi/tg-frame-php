<?php

namespace TgFramePhp\TgFramePhp;
use Telegram\Bot\Api;
use TgFramePhp\TgFramePhp\CommandHandler;

class Framework
{
    private $botToken;
    private $botOwnerId;
    private $commands;
    protected $telegram;
    protected $commandHandler;

    public function __construct($botToken, $botOwnerId)
    {
        $this->botToken   = $botToken;
        $this->botOwnerId = $botOwnerId;
        $this->commands   = [];
        $this->telegram   = new Api($this->botToken);  // استخدام مكتبة Telegram SDK
        $this->commandHandler = new CommandHandler($this->telegram);  // تهيئة commandHandler
        

        echo "Framework Initialized\n";
        echo "Bot Token: {$this->botToken}\n";
        echo "Bot Owner ID: {$this->botOwnerId}\n";
    }
    
    public function getUpdates()
    {
        try {
            $updates = $this->telegram->getUpdates();
            if (!empty($updates)) {
                $this->setLastUpdateId(end($updates)['update_id'] + 1);
            }

            return $updates;
        } catch (\Exception $e) {
            echo "Error fetching updates: " . $e->getMessage();
            return [];
        }
    }
    
    
    
    public function sendMessage($chatId, $text)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        file_get_contents($url . '?' . http_build_query($data));
    }
    
    public function registerCommand($command, callable $handler)
    {
        $this->commands[$command] = $handler;
    }
    
    private function handleCommand($chatId, $command, $args)
	{
	    if (isset($this->commands[$command])) {
	        call_user_func($this->commands[$command], $chatId, $args);
	    } else {
	        $this->sendMessage($chatId, "Unknown command: $command");
	    }
	}
	
	private function handleMessageType($chatId, $message)
	{
	    if (isset($message['text'])) {
	        $this->sendMessage($chatId, "You said: {$message['text']}");
	    } elseif (isset($message['photo'])) {
	        $this->sendMessage($chatId, "I see you sent a photo!");
	    } elseif (isset($message['video'])) {
	        $this->sendMessage($chatId, "I see you sent a video!");
	    } else {
	        $this->sendMessage($chatId, "I'm not sure how to handle this type of message.");
	    }
	}
	
	public function handleMessages()
	{
	    $updates = $this->getUpdates();
	    
	    // قراءة معرفات الرسائل التي تم معالجتها من ملف
	    $processedMessages = $this->getProcessedMessages();
	
	    foreach ($updates as $update) {
	        $message = $update->getMessage();
	        $chatId = $message->getChat()->getId();
	        $messageId = $message->getMessageId();
	        $text = $message->getText();
	
	        // إذا كانت الرسالة قد تم معالجتها سابقًا، تخطها
	        if (in_array($messageId, $processedMessages)) {
	            continue;
	        }
	
	        // إضافة معرف الرسالة إلى المصفوفة وتخزينه
	        $processedMessages[] = $messageId;
	        $this->saveProcessedMessages($processedMessages);
	
	        // إذا كانت الرسالة أمرًا
	        $command = $this->parseCommand($text);
	        
	        if ($command) {
	            // إذا كان هناك أمر، قم بمعالجته
	            $args = substr($text, strlen($command)); // استخراج باقي الرسالة بعد الأمر
	            $this->commandHandler->handleCommand($chatId, $command, $args);
	        } else {
	            // إذا لم يكن أمرًا معتمدًا، تعامل مع الرسالة كرسالة نصية
	            $this->sendMessage($chatId, "You said: $text");
	        }
	    }
	}
	
	// دالة لحفظ معرفات الرسائل المعالجة
	private function saveProcessedMessages($messages)
	{
	    file_put_contents('processed_messages.json', json_encode($messages));
	}
	
	// دالة لاسترجاع معرفات الرسائل المعالجة
	private function getProcessedMessages()
	{
	    if (file_exists('processed_messages.json')) {
	        return json_decode(file_get_contents('processed_messages.json'), true);
	    }
	    return [];
	}
	
	private function parseCommand($text)
    {
        // التحقق إذا كانت الرسالة تبدأ بعلامة "/"
        if (substr($text, 0, 1) === '/') {
            // استخراج الأمر من النص
            $command = strtok($text, ' '); 
            return $command;
        }

        return null;  // إذا لم تكن هناك أوامر معتمدة
    }
	
	private function getLastUpdateId()
	{
	    if (file_exists('updates.log')) {
	        return (int) file_get_contents('updates.log');
	    }
	    return 0;
	}
	
	private function setLastUpdateId($updateId)
	{
	    file_put_contents('updates.log', $updateId);
	}
	
	
    
}