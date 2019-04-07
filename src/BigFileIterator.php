<?php
/**
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Copyright (c) 2019, Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.gnu.org/licenses/lgpl.html
 * @date 04/11/18 20:38
 *
 * Class BigFileIterator
 * The class iterates on big files in an efficient way.
 * Huge thanks to Mokhtar Ebrahim on his website : https://likegeeks.com/process-large-files-using-php/
 *
 * @link https://github.com/racine-p-a/vanitas
 */

class BigFileIterator
{
    protected $file;

    /**
     * BigFileIterator constructor.
     * @param $filename
     * @param string $mode
     * @throws Exception
     */
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