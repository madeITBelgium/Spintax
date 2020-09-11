<?php

use MadeITBelgium\Spintax\Spintax;

class SpintaxTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCountSingle()
    {
        $string = 'This is {my|your} spintax.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);
        $this->assertEquals(2, $content->count());
    }

    public function testCountMultiple()
    {
        $string = 'This is {my|your} {content|text}.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);

        $this->assertEquals(4, $content->count());
    }

    public function testGetAllSingle()
    {
        $string = 'This is {my|your} spintax.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);

        $this->assertEquals($content->getAll(), [
            0 => 'This is my spintax.',
            1 => 'This is your spintax.',
        ]);
    }

    public function testGetAllMultiple()
    {
        $string = 'This is {my|your} {content|text}.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);

        $this->assertEquals($content->getAll(), [
            0 => 'This is my content.',
            1 => 'This is my text.',
            2 => 'This is your content.',
            3 => 'This is your text.',
        ]);
    }

    public function testAllNested()
    {
        $string = 'This is {my nested {spintax|spuntext} formatted string|your nested {spintax|spuntext} formatted string} example.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);

        $this->assertEquals($content->getAll(), [
            'This is my nested spintax formatted string example.',
            'This is my nested spuntext formatted string example.',
            'This is your nested spintax formatted string example.',
            'This is your nested spuntext formatted string example.',
        ]);
    }

    public function testAllNested2()
    {
        $string = 'In order to {create|make} a spun {article|piece of content}, you {will|may} need to|you {must|will need to}} {rewrite|reword} the {article|content} in this {way|format}.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);

        $this->assertEquals($content->getAll(), [
            'In order to create rewrite the article in this way.',
            'In order to create rewrite the article in this format.',
            'In order to create rewrite the content in this way.',
            'In order to create rewrite the content in this format.',
            'In order to create reword the article in this way.',
            'In order to create reword the article in this format.',
            'In order to create reword the content in this way.',
            'In order to create reword the content in this format.',
            'In order to make rewrite the article in this way.',
            'In order to make rewrite the article in this format.',
            'In order to make rewrite the content in this way.',
            'In order to make rewrite the content in this format.',
            'In order to make reword the article in this way.',
            'In order to make reword the article in this format.',
            'In order to make reword the content in this way.',
            'In order to make reword the content in this format.',
            'In order to you must rewrite the article in this way.',
            'In order to you must rewrite the article in this format.',
            'In order to you must rewrite the content in this way.',
            'In order to you must rewrite the content in this format.',
            'In order to you must reword the article in this way.',
            'In order to you must reword the article in this format.',
            'In order to you must reword the content in this way.',
            'In order to you must reword the content in this format.',
            'In order to you will need to rewrite the article in this way.',
            'In order to you will need to rewrite the article in this format.',
            'In order to you will need to rewrite the content in this way.',
            'In order to you will need to rewrite the content in this format.',
            'In order to you will need to reword the article in this way.',
            'In order to you will need to reword the article in this format.',
            'In order to you will need to reword the content in this way.',
            'In order to you will need to reword the content in this format.',
        ]);
    }

    public function testParseAndPathNested()
    {
        $string = 'This is {my nested {spintax|spuntext} formatted string|your nested {spintax|spuntext} formatted string} example.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);

        $path = [0, 0];
        $txt = $content->generate($path);
        $this->assertEquals($txt, 'This is my nested spintax formatted string example.');
        $this->assertEquals($path, [0, 0]);
    }

    public function testParseAndPathNested2()
    {
        $string = '{In order to|To} {create|make} a spun {article|piece of content}, {{you|one|they|he|she} {will|may} need to|{one|you} {must|will need to}} {rewrite|reword} the {article|content} in this {way|format}.';
        $spintax = new Spintax();
        $content = $spintax->parse($string);

        $path = [0, 0, 0, 0, 0, 0, 0, 0, 0];
        $txt = $content->generate($path);
        $this->assertEquals($txt, 'In order to create a spun article, you will need to rewrite the article in this way.');
        $this->assertEquals($path, [0, 0, 0, 0, 0, 0, 0, 0, 0]);
    }

    public function testPerformance()
    {
        $string = '{In order to|To} {create|make} a spun {article| piece of content}, {{you|one|they|he|she} {will|may} need to|{one|you} {must|will need to}} {rewrite|reword} the {article|content} in this {way|format}. {In order to|To} {create|make} a spun {article| piece of content} {rewrite|reword} the {article|content} in this {way|format}.';
        for ($i = 0; $i < 0; $i++) {
            $string .= $string;
        }

        $startTime = microtime(true);
        $spintax = new Spintax();
        $content = $spintax->parse($string);
        $this->assertLessThan(0.5, microtime(true) - $startTime);

        $startTime = microtime(true);
        $path = [];
        $this->assertEquals(57344, $content->count());
        $this->assertLessThan(0.5, microtime(true) - $startTime);

        $startTime = microtime(true);
        $path = [];
        $content->generate($path);
        $this->assertLessThan(0.5, microtime(true) - $startTime);

        $startTime = microtime(true);
        $content->getAll();
        $this->assertLessThan(1, microtime(true) - $startTime);
    }
}
