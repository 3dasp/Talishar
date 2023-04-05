<?php

  error_reporting(E_ALL);

  //We should always have a player ID as a URL parameter
  $gameName=$_GET["gameName"];
  $playerID=$_GET["playerID"];
  $remainingHealth=$_GET["health"];

  //First we need to parse the game state from the file
  include "ZoneGetters.php";
  include "ParseGamestate.php";
  include "../HostFiles/Redirector.php";
  include "DecisionQueue.php";
  include "../WriteLog.php";

  $health = &GetZone($playerID, "Health");
  $health[0] = $remainingHealth;
  $encounter = &GetZone($playerID, "Encounter");
  $encounter->subphase = "AfterFight";
  if ($encounter->position < 9){
    $encounter->gold += 2;
  } else {
    $encounter->gold += 3;
  }
  //WriteLog($encounter->position);
  if($encounter->position == 8 && $health[0] <= 20){
    $health[0] = 20;
  }

  $deck = &GetZone($playerID, "Deck");
  for($i = 0; $i < count($deck); ++$i)
  {
    switch($deck[$i])
    {
      case "ROGUE611":
        if($health[0] <= 5)
        {
          WriteLog("Your Bloodstone grows in strength.");
          $deck[$i] = "ROGUE612";
        }
        break;
      case "ROGUE612":
        if($health[0] <= 5)
        {
          WriteLog("Your Bloodstone grows in strength.");
          $deck[$i] = "ROGUE613";
        }
        break;
      case "ROGUE613":
        if($health[0] <= 5)
        {
          WriteLog("Your Bloodstone grows in strength.");
          $deck[$i] = "ROGUE614";
        }
        break;
      case "ROGUE614":
        if($health[0] <= 5)
        {
          WriteLog("Your Bloodstone is perfect.");
          $deck[$i] = "ROGUE615";
        }
        break;
      case "ROGUE615":
        if($health[0] <= 5)
        {
          WriteLog("Something Ancient awakens within your Bloodstone. Your name escapes you. Perhaps you shouldn't have disturbed it.");
          $deck[$i] = "ROGUE616";
        }
        break;
      default: break;
    }
  }
  if($encounter->position != 17) {
    AddDecisionQueue("CHOOSECARD", $playerID, GetRandomCards("Reward,Class-Class-Talent-Generic"), "Reward,Class-Class-Talent-Generic");
    //AddDecisionQueue("CHOOSECARD", $playerID, GetRandomCards("Reward,Class-Class-Talent-Generic"), "Reward,Class-Class-Talent-Generic");
    AddDecisionQueue("SETENCOUNTER", $playerID, "009-PickMode");
  }
  else {
    $encounter->encounterID = "011";
    $encounter->subphase = "";
    InitializeEncounter($playerID);
  }

  include "WriteGamestate.php";

  header("Location: " . $redirectPath . "/Roguelike/NextEncounter.php?gameName=$gameName&playerID=" . $playerID);

  exit;


?>
