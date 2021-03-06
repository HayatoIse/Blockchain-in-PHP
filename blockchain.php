<?php

/**
 * Class Block
 */
class Block
{
    private $index;
    private $previousHash;
    private $timestamp;
    private $data;
    private $hash;

    /**
     * Block constructor.
     * @param $index
     * @param $previousHash
     * @param $timestamp
     * @param $data
     * @param $hash
     */
    function __construct($index, $previousHash, $timestamp, $data, $hash)
    {
        $this->index = $index;
        $this->previousHash = $previousHash;
        $this->timestamp = $timestamp;
        $this->data = $data;
        $this->hash = $hash;
    }

    /**
     * @return mixed
     */
    function getIndex()
    {
        return $this->index;
    }

    /**
     * @return mixed
     */
    function getPreviousHash()
    {
        return $this->previousHash;
    }

    /**
     * @return mixed
     */
    function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return mixed
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    function getHash()
    {
        return $this->hash;
    }
}

/**
 * Class Blockchain
 */
class Blockchain
{
    private $blockchain = [];

    /**
     * Blockchain constructor.
     */
    function __construct()
    {
        $this->blockchain[] = $this->getGenesisBlock();
    }

    /**
     * @return array
     */
    function getBlockchain()
    {
        return $this->blockchain;
    }

    // Genesis block generation / acquisition
    /**
     * @return Block
     */
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
    /**
     * @param $index
     * @param $previousHash
     * @param $timestamp
     * @param $data
     * @return string
     */
    function calculateHash($index, $previousHash, $timestamp, $data)
    {
        return hash('sha256', $index . $previousHash . $timestamp . $data);
    }

    // Generate hash from block
    /**
     * @param Block $block
     * @return string
     */
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
    /**
     * @return Block
     */
    function getLatestBlock(): Block
    {
        return $this->blockchain[count($this->blockchain) - 1];
    }

    // Generate the next block
    /**
     * @param $blockData
     * @return Block
     */
    function generateNextBlock($blockData): Block
    {
        $previousBlock = $this->getLatestBlock();
        $nextIndex = $previousBlock->getIndex() + 1;
        $nextTimestamp = (new DateTime())->getTimestamp() / 1000;
        $nextHash = $this->calculateHash($nextIndex, $previousBlock->getHash(), $nextTimestamp, $blockData);
        return new Block($nextIndex, $previousBlock->getHash(), $nextTimestamp, $blockData, $nextHash);
    }

    // Safety check of newly created block
    /**
     * @param Block $newBlock
     * @param Block $previousBlock
     * @return bool
     */
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
    /**
     * @param Blockchain $newBlockchain
     */
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
    /**
     * @param Blockchain $blockchain
     * @return bool
     */
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
    /**
     * @param Block $newBlock
     */
    function addBlock(Block $newBlock)
    {
        if ($this->isValidNewBlock($newBlock, $this->getLatestBlock())) {
            $this->blockchain[] = $newBlock;
        }
    }

    /**
     * @param Blockchain $newBlockchain
     * @param string $name
     */
    function broadcast(Blockchain $newBlockchain, string $name)
    {
        echo "$name broadcast.\n";
        $size = count($newBlockchain->getBlockchain());
        $this->replaceChain($newBlockchain);
        $latestBlockHash = $this->getLatestBlock()->getHash();
        echo "$name new Blockchain. SIZE: $size, LATEST_BLOCK: $latestBlockHash\n";
    }
}