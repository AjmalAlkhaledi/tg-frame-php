<?php

namespace TgFramePhp\TgFramePhp;

class CommandHandler
{
    protected $bot;
    protected $welcomeUsersFile = __DIR__ . '/welcome_users.json';

    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    // دالة لمعالجة الأوامر
    public function handleCommand($chatId, $command, $args)
{
    // التحقق إذا كانت الرسالة نصًا غير أمر معتمد (وليس فقط رسالة موجهة للبوت)
    if (empty($command)) {
        $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => "You said: $args"
        ]);
        return;  // لا نحتاج لإرسال "Unknown command" إذا كانت مجرد رسالة نصية.
    }

    switch ($command) {
        case '/start':
            $this->startCommand($chatId);  // تأكد من أن هذه الدالة موجودة وتعمل بشكل صحيح
            break;
        case '/help':
            $this->helpCommand($chatId);
            break;
        case '/info':
            $this->infoCommand($chatId);
            break;
        default:
            // إرسال رد عند وجود أمر غير معروف
            $this->unknownCommand($chatId, $command);
    }
}
    // أمر /start مع التحقق إذا كان المستخدم قد تم الترحيب به من قبل
    protected function startCommand($chatId)
{
    // إذا كان المستخدم لم يتم الترحيب به بعد
    if (!$this->hasWelcomed($chatId)) {
        $this->sendWelcomeMessage($chatId);
        $this->markAsWelcomed($chatId);
    }
    // أضف رسالة ترحيب حتى إذا كان قد تم الترحيب به مسبقًا
    $this->bot->sendMessage([
        'chat_id' => $chatId,
        'text' => 'Welcome to the bot! Type /help for assistance.'
    ]);
}

    protected function sendWelcomeMessage($chatId)
    {
        $this->bot->sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Welcome to the bot! Type /help for assistance.'
        ]);
    }

    // التحقق إذا كان المستخدم قد تم الترحيب به
    protected function hasWelcomed($chatId)
    {
        if (file_exists($this->welcomeUsersFile)) {
            $welcomeUsers = json_decode(file_get_contents($this->welcomeUsersFile), true);
            return in_array($chatId, $welcomeUsers);
        }
        return false;
    }

    // تسجيل المستخدم بعد أن تم الترحيب به
    protected function markAsWelcomed($chatId)
    {
        $welcomeUsers = [];
        if (file_exists($this->welcomeUsersFile)) {
            $welcomeUsers = json_decode(file_get_contents($this->welcomeUsersFile), true);
        }
        $welcomeUsers[] = $chatId;
        file_put_contents($this->welcomeUsersFile, json_encode($welcomeUsers));
    }

    // الأمر /help
    protected function helpCommand($chatId)
    {
        $this->bot->sendMessage([
            'chat_id' => $chatId, 
            'text' => 'Here are the commands you can use: /start, /help, /info'
        ]);
    }

    // الأمر /info
    protected function infoCommand($chatId)
    {
        $this->bot->sendMessage([
            'chat_id' => $chatId, 
            'text' => 'This bot is built using PHP and the Telegram Bot SDK.'
        ]);
    }

    // أمر غير معروف
    protected function unknownCommand($chatId, $command)
	{
	    $this->bot->sendMessage([
	        'chat_id' => $chatId,
	        'text' => "Unknown command: $command. Type /help for a list of available commands."
	    ]);
	}
}