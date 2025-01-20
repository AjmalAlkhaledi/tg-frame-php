<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use TgFramePhp\TgFramePhp\Framework;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$botToken = $_ENV['BOT_TOKEN'];
$botOwnerId = $_ENV['BOT_OWNER_ID'];

$framework = new Framework($botToken, $botOwnerId);

// تسجيل الأوامر
$framework->registerCommand('/start', function ($chatId, $args) use ($framework) {
    $framework->sendMessage($chatId, "Welcome to the bot!");
});

$framework->registerCommand('/help', function ($chatId, $args) use ($framework) {
    $framework->sendMessage($chatId, "Available commands:\n/start - Start the bot\n/help - Show this help message");
});

// تشغيل المعالج
$framework->handleMessages();