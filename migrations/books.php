<?php
include __DIR__ . "/../bootstrap.php";

$capsule->schema()->create('books', function ($table) {
    $table->increments('id');
    $table->string('title')->default('');
    $table->string('word')->default('');
    $table->string('lesson')->default('');
    $table->string('translate')->default('');//单词书的翻译，没有校对
    $table->string('details')->default('');//单词情景
    $table->string('phoneticOfUs')->default('');//美音音标
    $table->string('phoneticOfUk')->default('');//英音音标
    $table->string('audioOfVocabulary')->default('');//音频下载URL
    $table->string('audioOfCambridge')->default('');//音频下载URL
    $table->string('sourceOfVocabulary')->default('');//单词原页面URL
    $table->string('sourceOfCambridge')->default('');//单词原页面URL
    $table->string('translateOfCambridge')->default('');//剑桥上面的翻译
    $table->string('phoneticOfSB')->default('');//扇贝网的音标，因为是从接口或取得，所以格式比较完整
    $table->string('translateOfSb')->default('');//扇贝网官方的翻译
    $table->softDeletes();
    $table->timestamps();
});