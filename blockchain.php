<?php

class Block
{
    private $index;
    private $previousHash;
    private $timestamp;
    private $data;
    private $hash;

    function __construct($index, $previousHash, $timestamp, $data, $hash)
    {
        $this->index = $index;
        $this->previousHash = $previousHash;
        $this->timestamp = $timestamp;
        $this->data = $data;
        $this->hash = $hash;
    }

    function getIndex()
    {
        return $this->index;
    }

    function getPreviousHash()
    {
        return $this->previousHash;
    }

    function getTimestamp()
    {
        return $this->timestamp;
    }

    function getData()
    {
        return $this->data;
    }

    function getHash()
    {
        return $this->hash;
    }
}

class Blockchain
{
    private $blockchain = [];

    function __construct()
    {
        $this->blockchain[] = $this->getGenesisBlock();
    }

    function getBlockchain()
    {
        return $this->blockchain;
    }

    // Genesis block generation / acquisition
    function getGenesisBlock(): Block
    {
        return new Block(
            0,
            '0',
            1465154705,
            'my genesis block!',
            '816534932c2b7154836da6afc367695e6337db8a921823784c14378abed4f7d7'
        );
    }

    // Hash generation
    function calculateHash($index, $previousHash, $timestamp, $data)
    {
        return hash('sha256', $index . $previousHash . $timestamp . $data);
    }

    // Generate hash from block
    function calculateHashForBlock(Block $block): string
    {
        return $this->calculateHash(
            $block->getIndex(),
            $block->getPreviousHash(),
            $block->getTimestamp(),
            $block->getData()
        );
    }

    // Get the last block of the blockchain
    function getLatestBlock(): Block
    {
        return $this->blockchain[count($this->blockchain) - 1];
    }

    // Generate the next block
    function generateNextBlock($blockData): Block
    {
        $previousBlock = $this->getLatestBlock();
        $nextIndex = $previousBlock->getIndex() + 1;
        $nextTimestamp = (new DateTime())->getTimestamp() / 1000;
        $nextHash = $this->calculateHash($nextIndex, $previousBlock->getHash(), $nextTimestamp, $blockData);
        return new Block($nextIndex, $previousBlock->getHash(), $nextTimestamp, $blockData, $nextHash);
    }

    // Safety check of newly created block
    function isValidNewBlock($newBlock, $previousBlock)
    {
        if ($previousBlock->getIndex() + 1 !== $newBlock->getIndex()) {
            echo "Warning: Invalid index.\n";
            return false;
        } elseif ($previousBlock->getHash() !== $newBlock->getPreviousHash()) {
            echo "Warning: Invalid previous hash.\n";
            return false;
        } elseif ($this->calculateHashForBlock($newBlock) !== $newBlock->getHash()) {
            echo 'Warning: Invalid hash: ' . $this->calculateHashForBlock($newBlock) . ' ' . $newBlock->getHash() . "\n";
            return false;
        }
        return true;
    }

    // Select longest chain
    function replaceChain(Blockchain $newBlockchain)
    {
        $newBlocks = $newBlockchain->getBlockchain();

        if ($this->isValidChain($newBlockchain) && count($newBlocks) > count($this->blockchain)) {
            echo "Received blockchain is valid. Replacing current blockchain with received blockchain\n";
            $this->blockchain = $newBlocks;
        } else {
            echo "Received blockchain invalid\n";
        }
    }

    // Validity check of block chain
    function isValidChain(Blockchain $blockchain): bool
    {
        $blockchainToValidate = $blockchain->getBlockchain();

        // Check if genesis blocks match
        if ($blockchainToValidate[0]->getHash() !== $this->getGenesisBlock()->getHash()) {
            return false;
        }

        // Check validity of all blocks
        foreach ($blockchainToValidate as $index => $blockToValidate) {
            if (0 === $index || $this->isValidNewBlock($blockToValidate, $blockchainToValidate[$index - 1])) {
                continue;
            }
            return false;
        }
        return true;
    }

    // Block added to block chain
    function addBlock(Block $newBlock)
    {
        if ($this->isValidNewBlock($newBlock, $this->getLatestBlock())) {
            $this->blockchain[] = $newBlock;
        }
    }

    function broadcast(Blockchain $newBlockchain, string $name)
    {
        echo "$name broadcast.\n";
        $size = count($newBlockchain->getBlockchain());
        $this->replaceChain($newBlockchain);
        $latestBlockHash = $this->getLatestBlock()->getHash();
        echo "$name new Blockchain. SIZE: $size, LATEST_BLOCK: $latestBlockHash\n";
    }
}