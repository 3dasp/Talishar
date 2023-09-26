<?php

function PutItemIntoPlay($item, $steamCounterModifier = 0)
{
  global $currentPlayer;
  PutItemIntoPlayForPlayer($item, $currentPlayer, $steamCounterModifier);
}

function PutItemIntoPlayForPlayer($item, $player, $steamCounterModifier = 0, $number = 1)
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  if(!DelimStringContains(CardSubType($item), "Item") && $item != "DTD164") return;
  $items = &GetItems($player);
  $myHoldState = ItemDefaultHoldTriggerState($item);
  if($myHoldState == 0 && HoldPrioritySetting($player) == 1) $myHoldState = 1;
  $theirHoldState = ItemDefaultHoldTriggerState($item);
  if($theirHoldState == 0 && HoldPrioritySetting($otherPlayer) == 1) $theirHoldState = 1;
  for($i = 0; $i < $number; ++$i) {
    $uniqueID = GetUniqueId();
    $steamCounters = SteamCounterLogic($item, $player, $uniqueID) + $steamCounterModifier;
    $index = count($items);
    array_push($items, $item);
    array_push($items, $steamCounters);
    array_push($items, 2);
    array_push($items, ItemUses($item));
    array_push($items, $uniqueID);
    array_push($items, $myHoldState);
    array_push($items, $theirHoldState);
    if(HasCrank($item, $player)) Crank($player, $index);
  }
}

function ItemUses($cardID)
{
  switch($cardID) {
    case "EVR070": return 3;
    default: return 1;
  }
}

function PayItemAbilityAdditionalCosts($cardID, $from)
{
  global $currentPlayer, $CS_PlayIndex, $combatChain;
  $index = GetClassState($currentPlayer, $CS_PlayIndex);
  switch($cardID) {
    case "WTR162":
    case "WTR170": case "WTR171": case "WTR172":
    case "ELE143": case "ELE172": case "ELE201":
    case "EVR176": case "EVR177": case "EVR178":
    case "EVR179": case "EVR180": case "EVR181":
    case "EVR182": case "EVR183": case "EVR184":
    case "EVR185": case "EVR186": case "EVR187":
    case "OUT054":
      DestroyItemForPlayer($currentPlayer, $index);
      break;
    case "ARC035":
      $items = &GetItems($currentPlayer);
      AddAdditionalCost($currentPlayer, $items[$index+1]);
      DestroyItemForPlayer($currentPlayer, $index);
      break;
    case "ARC010": case "ARC018":
      $items = &GetItems($currentPlayer);
      if($from == "PLAY" && $items[$index+1] > 0 && count($combatChain) > 0) {
        $items[$index+1] -= 1;
        $items[$index+2] = 1;
      }
      break;
    case "CRU105":
      $items = &GetItems($currentPlayer);
      if($from == "PLAY" && $items[$index+1] > 0) {
        $items[$index+1] -= 1;
        AddAdditionalCost($currentPlayer, "PAID");
      }
      break;
    case "EVO075": case "EVO076": case "EVO077":
      RemoveItem($currentPlayer, $index);
      $deck = new Deck($currentPlayer);
      $deck->AddBottom($cardID, from:"PLAY");
      break;
    default: break;
  }
}

function ItemPlayAbilities($cardID, $from)
{
  global $currentPlayer;
  $items = &GetItems($currentPlayer);
  for($i = count($items) - ItemPieces(); $i >= 0; $i -= ItemPieces()) {
    $remove = false;
    switch($items[$i]) {
      case "EVR189":
        if($from == "BANISH") {
          $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card name to banish with Talisman of Cremation");
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRDISCARD");
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
          AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "THEIRDISCARD:sameName=", 1);
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZBANISH", $currentPlayer, "GY,-," . $currentPlayer, 1);
          AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
          $remove = true;
        }
        break;
      default: break;
    }
    if($remove) DestroyItemForPlayer($currentPlayer, $i);
  }
}

function RemoveItem($player, $index)
{
  return DestroyItemForPlayer($player, $index, true);
}

function DestroyItemForPlayer($player, $index, $skipDestroy=false)
{
  global $CS_NumItemsDestroyed;
  $items = &GetItems($player);
  if(!$skipDestroy) {
    if(CardType($items[$index]) != "T" && GoesWhereAfterResolving($items[$index], "PLAY", $player) == "GY")
      AddGraveyard($items[$index], $player, "PLAY");
    IncrementClassState($player, $CS_NumItemsDestroyed);
  }
  $cardID = $items[$index];
  for($i = $index + ItemPieces() - 1; $i >= $index; --$i) {
    if($items[$i] == "DYN492c") {
      $indexWeapon = FindCharacterIndex($player, "DYN492a");
      DestroyCharacter($player, $indexWeapon);
      $indexEquipment = FindCharacterIndex($player, "DYN492b");
      DestroyCharacter($player, $indexEquipment);
    }
    unset($items[$i]);
  }
  $items = array_values($items);
  return $cardID;
}

function StealItem($srcPlayer, $index, $destPlayer)
{
  $srcItems = &GetItems($srcPlayer);
  $destItems = &GetItems($destPlayer);
  for($i = 0; $i < ItemPieces(); ++$i) {
    array_push($destItems, $srcItems[$index+$i]);
    unset($srcItems[$index+$i]);
  }
  $srcItems = array_values($srcItems);
}

function GetItemGemState($player, $cardID)
{
  global $currentPlayer;
  $items = &GetItems($player);
  $offset = ($currentPlayer == $player ? 5 : 6);
  $state = 0;
  for ($i = 0; $i < count($items); $i += ItemPieces()) {
    if ($items[$i] == $cardID && $items[$i + $offset] > $state) $state = $items[$i + $offset];
  }
  return $state;
}

function ItemHitEffects($attackID)
{
  global $mainPlayer, $defPlayer;
  $attackSubType = CardSubType($attackID);
  $items = &GetItems($mainPlayer);
  for($i = count($items) - ItemPieces(); $i >= 0; $i -= ItemPieces()) {
    $remove = false;
    switch($items[$i]) {
      case "DYN094":
        if($attackSubType == "Gun" && ClassContains($attackID, "MECHANOLOGIST", $mainPlayer)) {
          AddLayer("TRIGGER", $mainPlayer, $items[$i], "-", "-", $items[$i+4]);
        }
        break;
      case "EVO084": case "EVO085": case "EVO086":
        if($items[$i] == "EVO084") $amount = 4;
        else if($items[$i] == "EVO085") $amount = 3;
        else $amount = 2;
        if(IsHeroAttackTarget() && CardType($attackID) == "AA" && ClassContains($attackID, "MECHANOLOGIST", $mainPlayer)) {
          DamageTrigger($defPlayer, $amount, "DAMAGE", $items[$i]);
          $remove = true;
        }
        break;
      default: break;
    }
    if($remove) DestroyItemForPlayer($mainPlayer, $i);
  }
}

function ItemTakeDamageAbilities($player, $damage, $type)
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  $items = &GetItems($player);
  $preventable = CanDamageBePrevented($otherPlayer, $damage, $type);
  for($i=count($items) - ItemPieces(); $i >= 0 && $damage > 0; $i -= ItemPieces()) {
    switch($items[$i]) {
      case "CRU104":
        if($damage > $items[$i+1]) { if($preventable) $damage -= $items[$i+1]; $items[$i+1] = 0; }
        else { $items[$i+1] -= $damage; if($preventable) $damage = 0; }
        if($items[$i+1] <= 0) DestroyItemForPlayer($player, $i);
    }
  }
  return $damage;
}

function ItemStartTurnAbility($index)
{
  global $mainPlayer;
  $mainItems = &GetItems($mainPlayer);
  switch($mainItems[$index]) {
    case "ARC007": case "ARC035": case "EVR069": case "EVR071":
      AddLayer("TRIGGER", $mainPlayer, $mainItems[$index], "-", "-", $mainItems[$index + 4]);
      break;
    case "EVO084": case "EVO085": case "EVO086":
    case "EVO087": case "EVO088": case "EVO089":
      if($mainItems[$index+1] > 0) --$mainItems[$index+1];
      else DestroyItemForPlayer($mainPlayer, $index);
      break;
    default: break;
  }
}

function ItemEndTurnAbilities()
{
  global $mainPlayer;
  $items = &GetItems($mainPlayer);
  for($i = count($items) - ItemPieces(); $i >= 0; $i -= ItemPieces()) {
    $remove = false;
    switch($items[$i]) {
      case "EVR188":
        $remove = TalismanOfBalanceEndTurn();
        break;
      default: break;
    }
    if($remove) DestroyItemForPlayer($mainPlayer, $i);
  }
}

function ItemDamageTakenAbilities($player, $damage)
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  $items = &GetItems($otherPlayer);
  for($i = count($items) - ItemPieces(); $i >= 0; $i -= ItemPieces()) {
    $remove = false;
    switch($items[$i]) {
      case "EVR193":
        if(IsHeroAttackTarget() && $damage == 2) {
          WriteLog("Talisman of Warfare destroyed both player's arsenal");
          DestroyArsenal(1);
          DestroyArsenal(2);
          $remove = true;
        }
        break;
      default: break;
    }
    if($remove) DestroyItemForPlayer($otherPlayer, $i);
  }
}

function SteamCounterLogic($item, $playerID, $uniqueID)
{
  global $CS_NumBoosted;
  $counters = ETASteamCounters($item);
  switch($item) {
    case "CRU104":
      $counters += GetClassState($playerID, $CS_NumBoosted);
      break;
    default: break;
  }
  if(ClassContains($item, "MECHANOLOGIST", $playerID)) {
    $items = &GetItems($playerID);
    for($i=count($items)-ItemPieces(); $i>=0; $i-=ItemPieces()) {
      if($items[$i] == "DYN093") {
        AddLayer("TRIGGER", $playerID, $items[$i], $uniqueID, "-", $items[$i+4]);
      }
    }
  }
  return $counters;
}


?>
