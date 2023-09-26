<?php

  function TCCPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
  {
    global $mainPlayer, $currentPlayer, $defPlayer;
    $rv = "";
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID) {
      case "TCC035":
        AddCurrentTurnEffect($cardID, $defPlayer);
        return "";
      case "TCC051":
        Draw(1);
        Draw(2);
        return "";
      case "TCC052":
        PlayAura("TCC107", 1);
        PlayAura("TCC107", 2);
        return "";
      case "TCC053":
        PlayAura("TCC105", 1);
        PlayAura("TCC105", 2);
        return "";
      case "TCC054":
        PlayAura("WTR225", 1);
        PlayAura("WTR225", 2);
        return "";
      case "TCC057":
        $numPitch = SearchCount(SearchPitch($currentPlayer));
        AddCurrentTurnEffect($cardID . "," . ($numPitch*2), $currentPlayer);
        return "";
      case "TCC058": case "TCC062": case "TCC075":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "TCC061":
        MZMoveCard($currentPlayer, "MYDISCARD:class=BARD;type=AA", "MYHAND", may:false, isSubsequent:false);
        return "";
      case "TCC064":
        PlayAura("WTR225", $otherPlayer);
        return "";
      case "TCC065":
        GainHealth(1, $otherPlayer);
        return "";
      case "TCC066": case "TCC067"://TODO: Add right Aura
        PlayAura("DTD232", $otherPlayer);
        return "";
      case "TCC068":
        Draw($otherPlayer);
        return "";
      case "TCC069":
        MZMoveCard($otherPlayer, "MYDISCARD:type=AA", "MYBOTDECK", may:true);
        return "";
      case "TCC079":
        Draw($currentPlayer);
        return "";
      case "TCC080":
        GainResources($currentPlayer, 1);
        return "";
      case "TCC082":
        BanishCardForPlayer("DYN065", $currentPlayer, "-", "TT", $currentPlayer);
        return "";
      case "TCC086": case "TCC094":
        AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        break;
      default: return "";
    }
  }


  function EVOPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
  {
    global $mainPlayer, $currentPlayer, $defPlayer, $layers;
    global $CS_NamesOfCardsPlayed, $CS_NumBoosted, $CS_PlayIndex, $CS_NumItemsDestroyed;
    $rv = "";
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID) {
      case "EVO004": case "EVO005":
        PutItemIntoPlayForPlayer("EVO234", $currentPlayer, 2);
        return "";
      case "EVO007": case "EVO008":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO009":
        $evoAmt = EvoUpgradeAmount($currentPlayer);
        if($evoAmt >= 3) GiveAttackGoAgain();
        if($evoAmt >= 4) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO014":
        MZMoveCard($mainPlayer, "MYBANISH:class=MECHANOLOGIST;type=AA", "MYTOPDECK");
        AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-", 1);
        return "";
      case "EVO015":
        AddDecisionQueue("GAINRESOURCES", $mainPlayer, "2");
        return "";
      case "EVO016":
        AddCurrentTurnEffectNextAttack($cardID, $mainPlayer);
        return "";
      case "EVO017":
        AddDecisionQueue("GAINACTIONPOINTS", $mainPlayer, "1");
        return "";
      case "EVO058":
        if(IsHeroAttackTarget())
        {
          $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
          AddDecisionQueue("PASSPARAMETER", $otherPlayer, EvoUpgradeAmount($currentPlayer), 1);
          AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("APPENDLASTRESULT", $otherPlayer, "-{0}", 1);
          AddDecisionQueue("PREPENDLASTRESULT", $otherPlayer, "{0}-", 1);
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose " . EvoUpgradeAmount($currentPlayer) . " card(s)", 1);
          AddDecisionQueue("MULTICHOOSEHAND", $otherPlayer, "<-", 1);
          AddDecisionQueue("IMPLODELASTRESULT", $otherPlayer, ",", 1);
          AddDecisionQueue("SETDQVAR", $currentPlayer, "1");
          AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "<-", 1);
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{1}", 1);
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card", 1);
          AddDecisionQueue("SPECIFICCARD", $otherPlayer, "PULSEWAVEHARPOONFILTER", 1);
          AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
          AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
          AddDecisionQueue("ADDCARDTOCHAIN", $otherPlayer, "HAND", 1);
        }
        return "";
      case "EVO059":
        WriteLog("This is a partially manual card. Must block with " . EvoUpgradeAmount($currentPlayer) . " equipment with -1 def counters if able");
        return "";
      case "EVO061": case "EVO062": case "EVO063":
        WriteLog("This is a partially manual card. Do not block with attack action cards with cost less than " . EvoUpgradeAmount($currentPlayer));
        return "";
      case "EVO070":
        if($from == "PLAY") DestroyTopCard($currentPlayer);
        break;
      case "EVO075":
        if($from == "PLAY") GainResources($currentPlayer, 1);
        return "";
      case "EVO076":
        if($from == "PLAY") GainHealth(2, $currentPlayer);
        return "";
      case "EVO077":
        if($from == "PLAY")
        {
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Crank to get a steam counter", 1);
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:hasCrank=true");
          AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZADDSTEAMCOUNTER", $currentPlayer, "-", 1);
        }
        return "";
      case "EVO087": case "EVO088": case "EVO089":
        if($from == "PLAY") AddCurrentTurnEffect($cardID, $currentPlayer);
        $index = GetClassState($currentPlayer, $CS_PlayIndex);
        $items = &GetItems($currentPlayer);
        --$items[$index+1];
        if($items[$index+1] <= 0) DestroyItemForPlayer($currentPlayer, $index);
        return "";
      case "EVO101":
        $numScrap = 0;
        $costAry = explode(",", $additionalCosts);
        for($i=0; $i<count($costAry); ++$i) if($costAry[$i] == "SCRAP") ++$numScrap;
        if($numScrap > 0) GainResources($currentPlayer, $numScrap * 2);
        return "";
      case "EVO108": case "EVO109": case "EVO110":
        if($additionalCosts == "SCRAP") PlayAura("WTR225", $currentPlayer);
        return "";
      case "EVO111": case "EVO112": case "EVO113":
        if(GetClassState($currentPlayer, $CS_NumItemsDestroyed) > 0) GiveAttackGoAgain();
        return "";
      case "EVO126": case "EVO127": case "EVO128":
        if($additionalCosts == "SCRAP") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO129": case "EVO130": case "EVO131":
        if($additionalCosts == "SCRAP") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO132": case "EVO133": case "EVO134":
        if($additionalCosts == "SCRAP") {
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Crank to get a steam counter", 1);
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:hasCrank=true");
          AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZADDSTEAMCOUNTER", $currentPlayer, "-", 1);
        }
        return "";
      case "EVO135": case "EVO136": case "EVO137":
        if($additionalCosts == "SCRAP") GainResources($currentPlayer, 1);
        return "";
      case "EVO140":
        for($i=0; $i<$resourcesPaid; $i+=2) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO144":
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS:hasSteamCounter=true&THEIRCHAR:hasSteamCounter=true&MYITEMS:hasSteamCounter=true&MYCHAR:hasSteamCounter=true");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an equipment, item, or weapon. Remove all steam counters from it.");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVESTEAMCOUNTER", $currentPlayer, "-", 1);
        AddDecisionQueue("SYSTEMFAILURE", $currentPlayer, "<-", 1);
        return "";
      case "EVO155": case "EVO156": case "EVO157":
        if(GetClassState($currentPlayer, $CS_NumBoosted) >= 2) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO222": case "EVO223": case "EVO224":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        MZMoveCard($currentPlayer, "MYBANISH:sameName=ARC036", "", may:true);
        AddDecisionQueue("PUTPLAY", $currentPlayer, "0", 1);
        return "";
      case "EVO225": case "EVO226": case "EVO227":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO228": case "EVO229": case "EVO230":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a Hyper Driver to get a steam counter", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:sameName=ARC036");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZADDSTEAMCOUNTER", $currentPlayer, "-", 1);
        return "";
      case "EVO235":
        $options = GetChainLinkCards(($currentPlayer == 1 ? 2 : 1), "AA");
        if($options != "") {
          AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, $options);
          AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $currentPlayer, -1, 1);
        }
        return "";
      case "EVO239":
        $cardsPlayed = explode(",", GetClassState($currentPlayer, $CS_NamesOfCardsPlayed));
        for($i=0; $i<count($cardsPlayed); ++$i) {
          if(CardName($cardsPlayed[$i]) == "Wax On") {
            PlayAura("CRU075", $currentPlayer);
            break;
          }
        }
        return "";
      case "EVO242":
        $xVal = $resourcesPaid/2;
        PlayAura("ARC112", $currentPlayer, $xVal);
        if($xVal >= 6) {
          DiscardRandom($otherPlayer);
          DiscardRandom($otherPlayer);
          DiscardRandom($otherPlayer);
        }
        return "";
      case "EVO245":
        Draw($currentPlayer);
        if(IsRoyal($currentPlayer)) Draw($currentPlayer);
        PrependDecisionQueue("OP", $currentPlayer, "BANISHHAND", 1);
        if(SearchCount(SearchHand($currentPlayer, pitch:1)) >= 2) {
          PrependDecisionQueue("ELSE", $currentPlayer, "-");
          PitchCard($currentPlayer, "MYHAND:pitch=1");
          PitchCard($currentPlayer, "MYHAND:pitch=1");
          PrependDecisionQueue("NOPASS", $currentPlayer, "-");
          PrependDecisionQueue("YESNO", $currentPlayer, "if you want to pitch 2 red cards");
        }
        return "";
      case "EVO247":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO434":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO435":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO436":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "EVO437":
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=W");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a weapon to attack an additional time");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $currentPlayer, "ADDITIONALUSE", 1);
        return "";
      case "EVO446":
        Draw($currentPlayer);
        MZMoveCard($currentPlayer, "MYHAND", "MYTOPDECK", silent:true);
        return "";
      case "EVO447":
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Crank to get a steam counter", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:hasCrank=true");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZADDSTEAMCOUNTER", $currentPlayer, "-", 1);
        return "";
      case "EVO448":
        MZMoveCard($mainPlayer, "MYHAND:subtype=Item;maxCost=1", "", may:true);
        AddDecisionQueue("PUTPLAY", $mainPlayer, "0", 1);
        return "";
      case "EVO449":
        PlayAura("WTR225", $currentPlayer);
        return "";
      default: return "";
    }
  }

?>
