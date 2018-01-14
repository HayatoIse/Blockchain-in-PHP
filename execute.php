<?php

require_once 'blockchain.php';
require_once 'vendor/autoload.php';

use Amp\Loop;

// Initialize blockchain
$masterBlockchain = new Blockchain();
function createMiner(Blockchain &$masterBlockchain, string $name)
{
    $msInterval = rand(800, 5000);
    Loop::repeat($msInterval, function () use (&$masterBlockchain, $name) {
        $myBlockchain = clone $masterBlockchain;
        $blockCount = rand(1, 5);
        for ($i = 0; $i < $blockCount; $i++) {
            // Add block
            $newBlock = $myBlockchain->generateNextBlock('dummy_block_data');
            $newBlockHash = $newBlock->getHash();
            $myBlockchain->addBlock($newBlock);
            echo "$name add block. BLOCK: $newBlockHash\n";
        }
        // After creating a new block, broadcast it
        $masterBlockchain->broadcast($myBlockchain, $name);
    });
}

echo "=== START SIMULATE ===\n";
Loop::run(function () use (&$masterBlockchain) {
    createMiner($masterBlockchain, 'Miner1');
    createMiner($masterBlockchain, 'Miner2');
    createMiner($masterBlockchain, 'Miner3');
});