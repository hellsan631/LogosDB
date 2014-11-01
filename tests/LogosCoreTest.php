<?php

    class LogosCoreTest extends PHPUnit_Extensions_Database_TestCase{

        public function inputJson(){
            return [
                ['{"type":"bar","user":"true"}', true],
                ['[{"type":"bar","user":"true"},{"type":"drink","user":"false"}]', true],
                [["type" => "bar", "user" => "true"],false],
                [["Hello World"],false],
                [["{Hello World}"],false]
            ];
        }

        /**
         * @dataProvider inputJson
         */
        public function testCheckIsJson($jsonString, $expected){

            $this->assertEquals($expected, isJson($jsonString));

        }

    }