<?php

  function MONIllusionistPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer;
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID)
    {
      case "MON001": case "MON002":
        PlayAura("MON104", $currentPlayer);
        return "";
      case "MON090":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "MON091":
        if(!IsAllyAttackTarget()) {
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
          AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
          AddDecisionQueue("ADDBOTDECK", $otherPlayer, "-", 1);
          AddDecisionQueue("DRAW", $otherPlayer, "-", 1);
        }
        return "";
      case "MON092": PlayAura("MON104", $currentPlayer, 3); return "";
      case "MON093": PlayAura("MON104", $currentPlayer, 2); return "";
      case "MON094": PlayAura("MON104", $currentPlayer, 1); return "";
      case "MON095": case "MON096": case "MON097":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      default: return "";
    }
  }

  function MONIllusionistHitEffect($cardID)
  {
    global $combatChainState, $CCS_GoesWhereAfterLinkResolves, $mainPlayer, $defPlayer;
    switch($cardID)
    {
      case "MON004":
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL";
        Draw($mainPlayer);
        Draw($mainPlayer);
        break;
      case "MON007":
        if(!IsAllyAttackTarget()) {
          AddCurrentTurnEffect($cardID, $defPlayer);
          AddNextTurnEffect($cardID, $defPlayer);
        }
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL";
        break;
      case "MON008": case "MON009": case "MON010": $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL"; break;
      case "MON014": case "MON015": case "MON016":
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL";
        PlayAura("MON104", $mainPlayer);
        break;
      case "MON017": case "MON018": case "MON019":
        DealArcane(1, 0, "PLAYCARD", $cardID, false, $mainPlayer);
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL";
        break;
      case "MON020": case "MON021": case "MON022":
        AddDecisionQueue("FINDINDICES", $mainPlayer, $cardID);
        AddDecisionQueue("MAYCHOOSEDISCARD", $mainPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEDISCARD", $mainPlayer, "-", 1);
        AddDecisionQueue("MULTIADDTOPDECK", $mainPlayer, "-", 1);
        AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
        AddDecisionQueue("WRITELOG", $mainPlayer, "<0> was selected.", 1);
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL";
        break;
      case "MON023": case "MON024": case "MON025": $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL"; break;
      case "MON026": case "MON027": case "MON028": $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL"; break;
      default: break;
    }
  }

  function HasPhantasm($cardID)
  {
    switch($cardID)
    {
      case "MON004": case "MON007": case "MON008": case "MON009": case "MON010": case "MON014":
      case "MON015": case "MON016": case "MON017": case "MON018": case "MON019": case "MON020":
      case "MON021": case "MON022": case "MON023": case "MON024": case "MON025": case "MON026":
      case "MON027": case "MON028": case "MON091": case "MON098": case "MON099": case "MON100":
      case "MON101": case "MON102": case "MON103": return true;
      case "EVR138": FractalReplicationStats("HasPhantasm");
      case "EVR139": case "EVR144": case "EVR145": case "EVR146": case "EVR147": case "EVR148": case "EVR149": return true;
      case "UPR021": case "UPR022": case "UPR023": case "UPR027": case "UPR028": case "UPR029": case "UPR153": case "UPR551": return true;
      case "DYN215": case "DYN216": case "DYN224": case "DYN225": case "DYN226": case "DYN227": case "DYN228": case "DYN229":
        return true;
      default: return false;
    }
  }

  function IsPhantasmActive()
  {
    global $combatChain, $mainPlayer, $combatChainState, $CCS_WeaponIndex;
    if(count($combatChain) == 0) return false;
    if((SearchCurrentTurnEffects("MON090", $mainPlayer) && CardType($combatChain[0]) == "A") || SearchCurrentTurnEffects("EVR142", $mainPlayer) || SearchCurrentTurnEffects("UPR154", $mainPlayer) || SearchCurrentTurnEffects("UPR412", $mainPlayer)) { return false; }
    if(SearchCurrentTurnEffectsForCycle("EVR150", "EVR151", "EVR152", $mainPlayer)) return true;
    if(SearchCurrentTurnEffectsForCycle("MON095", "MON096", "MON097", $mainPlayer)) return true;
    if(SearchCurrentTurnEffectsForCycle("UPR155", "UPR156", "UPR157", $mainPlayer)) return true;
    if($combatChainState[$CCS_WeaponIndex] != "-1" && DelimStringContains(CardSubType($combatChain[0]), "Ally"))
    {
      $allies = &GetAllies($mainPlayer);
      if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "UPR043")) return true;
      else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "DYN002") && $allies[$combatChainState[$CCS_WeaponIndex]] != "UPR415") return true;
      else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "DYN003") && $allies[$combatChainState[$CCS_WeaponIndex]] != "UPR416") return true;
      else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "DYN004") && $allies[$combatChainState[$CCS_WeaponIndex]] != "UPR413") return true;
    }
    return HasPhantasm($combatChain[0]);
  }

  function ProcessPhantasmOnBlock($index)
  {
    global $mainPlayer;
    if(IsPhantasmActive() && DoesBlockTriggerPhantasm($index)) AddLayer("LAYER", $mainPlayer, "PHANTASM");
  }

  function DoesBlockTriggerPhantasm($index)
  {
    global $combatChain, $mainPlayer, $defPlayer;
    if(CardType($combatChain[$index]) != "AA") return false;
    if(ClassContains($combatChain[$index], "ILLUSIONIST", $defPlayer)) return false;
    $attackID = $combatChain[0];
    $av = AttackValue($combatChain[$index]);
    $origAV = $av;
    if($attackID == "MON008" || $attackID == "MON009" || $attackID == "MON010") --$av;
    $av += AuraAttackModifiers($index);
    $av += $combatChain[$index+5]; //Attack Modifiers
    return $av >= 6;
  }

  function IsPhantasmStillActive()
  {
    global $combatChain, $mainPlayer, $combatChainState, $CCS_WeaponIndex;
    if(count($combatChain) == 0) return false;
    $blockGreaterThan6 = false;
    for($i=CombatChainPieces(); $i<count($combatChain); $i+=CombatChainPieces())
    {
      if(DoesBlockTriggerPhantasm($i)) $blockGreaterThan6 = true;
    }
    if(!$blockGreaterThan6) return false;
    if((SearchCurrentTurnEffects("MON090", $mainPlayer) && CardType($combatChain[0]) == "A") || SearchCurrentTurnEffects("EVR142", $mainPlayer) || SearchCurrentTurnEffects("UPR154", $mainPlayer) || SearchCurrentTurnEffects("UPR412", $mainPlayer)) { return false; }
    return true;
  }

  function PhantasmLayer()
  {
    global $combatChain, $mainPlayer, $combatChainState, $CCS_WeaponIndex, $CS_NumPhantasmAADestroyed, $defPlayer, $turn, $layers;
    if(IsPhantasmStillActive())
    {
      $attackID = $combatChain[0];
      WriteLog(CardLink($attackID, $attackID) . " is destroyed by phantasm.");
      if($combatChainState[$CCS_WeaponIndex] != "-1" && DelimStringContains(CardSubType($combatChain[0]), "Ally")) DestroyAlly($mainPlayer, $combatChainState[$CCS_WeaponIndex]);
      if(ClassContains($attackID, "ILLUSIONIST", $mainPlayer))
      {
        GhostlyTouchPhantasmDestroy();
      }
      AttackDestroyed($attackID);
      if(CardType($attackID) == "AA")
      {
        IncrementClassState($mainPlayer, $CS_NumPhantasmAADestroyed);
        CloseCombatChain();
      }
      ProcessDecisionQueue();
    }
    else {
      $turn[0] = "D";
      $currentPlayer = $mainPlayer;
      for($i=count($layers)-LayerPieces(); $i >= 0; $i-=LayerPieces())
      {
        if($layers[$i] == "DEFENDSTEP" || ($layers[$i] == "LAYER" && $layers[$i+2] == "PHANTASM"))
        {
          for($j=$i; $j<($i+LayerPieces()); ++$j) unset($layers[$j]);
        }
      }
      $layers = array_values($layers);
    }
  }

  function HasSpectra($cardID)
  {
    switch($cardID)
    {
      case "MON005": case "MON006": case "MON011": case "MON012": case "MON013": return true;
      case "EVR140": case "EVR141": case "EVR142": case "EVR143": return true;
      default: return false;
    }
  }

  function GenesisStartTurnAbility()
  {
    global $mainPlayer;
    AddDecisionQueue("FINDINDICES", $mainPlayer, "HAND");
    AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Genesis: Choose a card to put in your hero's soul");
    AddDecisionQueue("MAYCHOOSEHAND", $mainPlayer, "<-", 1);
    AddDecisionQueue("MULTIREMOVEHAND", $mainPlayer, "-", 1);
    AddDecisionQueue("ADDSOUL", $mainPlayer, "HAND", 1);
    AddDecisionQueue("GENESIS", $mainPlayer, "-", 1);
  }

  function TheLibrarianEffect($player, $index)
  {
    $arsenal = &GetArsenal($player);
    --$arsenal[$index+2];
    ++$arsenal[$index+3];
    Draw($player);
    $log = CardLink("MON404","MON404") . " drew a card";
    if($arsenal[$index+3] == 3)
    {
      $log .= " and searched for a specialization card";
      MentorTrigger($player, $index);
    }
  }

?>
