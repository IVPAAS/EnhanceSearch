<?php

class ElasticCaptionsTest
{
    private $ch;
    private $captionMappings;
    private $entryStructure;
    private $captionStructure;
    private $createdEntryIds = [];
    private $possibleNames = [];
    private $possibleNamesCount ;

    public function init()
    {
        $this->ch = curl_init();
        $this->captionMappings = file_get_contents("./captionsMapping.json");
        $this->possibleNames = file("./entryNames.lines", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->possibleNamesCount = count($this->possibleNames);
    }

    public function deleteIndex()
    {
        curl_setopt($this->ch, CURLOPT_URL, "http://dev-backend27.dev.kaltura.com:9200/kaltura_entry_with_captions_index");
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($this->ch);
        $result = json_decode($result);
    }

    public function createIndex()
    {
        curl_setopt($this->ch, CURLOPT_URL, "http://dev-backend27.dev.kaltura.com:9200/kaltura_entry_with_captions_index");
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, "Content-Type: application/json");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS,  $this->captionMappings);
        $result = curl_exec($this->ch);
        $result = json_decode($result);
    }

    private function parseEntryBaseStructure()
    {
        $this->entryStructure = json_decode(file_get_contents("./entryBaseStructure.json"));
    }

    private function parseCaptionBaseStructure()
    {
        $this->captionStructure = json_decode(file_get_contents("./captionBaseStructure.json"));
    }

    public function fillEntryStructureWithRandomDetails($i)
    {
        $myRand = $i%$this->possibleNamesCount;
        $name = $this->possibleNames[$myRand];
        $this->entryStructure->name = $name;
        $this->entryStructure->description = "Description ".$myRand;
        $this->entryStructure->partnerId = $myRand;
        $this->entryStructure->status = $myRand%7;
    }

    public function fillCaptionStructureWithDetails($lines, $language)
    {
        $this->captionStructure->language = $language;
        $this->captionStructure->lines = $lines;

    }

    public function createEntryInElastic()
    {
        curl_setopt($this->ch, CURLOPT_URL, "http://dev-backend27.dev.kaltura.com:9200/kaltura_entry_with_captions_index/entry");
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, "Content-Type: application/json");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($this->entryStructure) );
        $result = curl_exec($this->ch);
        $resultJson = json_decode($result);
        if (!$resultJson->_id)
        {
            sleep(1);
            print_r('ERROR '.$result);
            return $this->createEntryInElastic();
        }
        else
        {
            return $resultJson->_id;
        }



    }

    public function createCaptionInElastic($entryId)
    {
        curl_setopt($this->ch, CURLOPT_URL, "http://dev-backend27.dev.kaltura.com:9200/kaltura_entry_with_captions_index/caption?parent=".$entryId);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, "Content-Type: application/json");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($this->captionStructure) );
        curl_exec($this->ch);

    }

    public function createEntries($numEntries)
    {
        $this->parseEntryBaseStructure();
        for ($i = 0 ; $i < $numEntries; $i++) {
            print_r('Creating entry '.$i.PHP_EOL);
            $this->fillEntryStructureWithRandomDetails($i);
            $this->createdEntryIds[] = $this->createEntryInElastic();
        }
    }



    public function createCaptions($numberOfDifferentCaptions)
    {
        $possible_languages = ["GB", "EN", "HE", "DE", "HY", "PN"];
        $file = fopen("./captionLines.lines", "r");
        $this->parseCaptionBaseStructure();
        $numberOfEntries = count($this->createdEntryIds);
        $captionsSoFar = 0;
        for ($entryIndex = 0; $captionsSoFar < $numberOfDifferentCaptions ; $entryIndex++,$captionsSoFar++, $entryIndex = $entryIndex % $numberOfEntries )
        {
            print_r('entry loop: '. $entryIndex.' caption index: '.$captionsSoFar.PHP_EOL);
            $entryId = $this->createdEntryIds[$entryIndex];

            $numOfCaptionLines = rand(1, MAX_NUM_LINES_PER_CAPTION);

            $captionLines = array();

            $line = null ;
            for ($i = 0; $i < $numOfCaptionLines ; $i++) {
                if (!feof($file)) {
                    $line = fgets($file);
                } else {
                    fclose($file);
                    $file = fopen("./captionLines.lines", "r");
                    $line = fgets($file);
                }
                $captionLines[] = array("content" => $line, "start" => $i, "end" =>$i+1);
            }

            $language = $possible_languages[$entryIndex%count($possible_languages)];
            $this->fillCaptionStructureWithDetails($captionLines, $language);
            $this->createCaptionInElastic($entryId);
        }
    }


};

// constants
define("NUM_ENTRIES", 3000);
define("NUM_CAPTIONS", 10000);
define("MAX_NUM_LINES_PER_CAPTION" ,200);
// flow
$captionsTest = new ElasticCaptionsTest();
$captionsTest->init();
$captionsTest->deleteIndex();
$captionsTest->createIndex();
sleep(1);
$captionsTest->createEntries(NUM_ENTRIES);
$captionsTest->createCaptions(NUM_CAPTIONS);
