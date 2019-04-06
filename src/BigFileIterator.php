<?php


class BigFileIterator
{
    protected $file;

    public function __construct($filename, $mode = "r")
    {
        if (!file_exists($filename)) {

            throw new Exception("File not found");

        }
        $this->file = new SplFileObject($filename, $mode);
    }

    protected function iterateText()
    {
        $count = 0;

        while (!$this->file->eof()) {

            yield $this->file->fgets();

            $count++;
        }
        return $count;
    }

    protected function iterateBinary($bytes)
    {
        $count = 0;

        while (!$this->file->eof()) {

            yield $this->file->fread($bytes);

            $count++;
        }
    }

    public function iterate($type = "Text", $bytes = NULL)
    {
        if ($type == "Text") {

            return new NoRewindIterator($this->iterateText());

        } else {

            return new NoRewindIterator($this->iterateBinary($bytes));
        }

    }
}