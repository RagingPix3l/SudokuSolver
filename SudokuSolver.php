<?php

class SudokuSolver {
    const MAX = 9;
    const MIN = 0;
    const SIZE = 9;

    public $puzzle;
    private $emptyCells = [];
    private $checkedCells = [];
    private $rows = [];
    private $cols = [];
    private $boxes = [];

    public $ntries = 0;
    public $iteration = 0;
    public static SudokuSolver $instance;

    public function __construct($grid)
    {
        self::$instance = $this;
        $this->rows = SudokuSolver::initArray();
        $this->cols = SudokuSolver::initArray();
        $this->boxes = SudokuSolver::initArray();
        $this->puzzle = $grid;
        throwIf(!$this->prevalidate());
        initBits();
        populateCells();
        throwIf(!$this->hasMore($this->emptyCells));
        $this->setupConnectedCells();
    }

    public static function initArray():array {
        $ret = [];
        for ($i = 0;$i<=SIZE;++$i){
            $ret [$i] = 0;
        }
        return $ret;
    }

    private function initBits():void {
        for ($i = 0; $i < $this->SIZE;++$i){
            for ($j = 0;$j < $this->SIZE; ++$j){
                if ($this->puzzle[i][j]>0){
                    throwIf($this->isBitSet($i,$j,$this->puzzle[$i][$j]));
                    toggleBit($i,$j);
                }
            }
        }
    }
    private function isBitSet(int $y, int $x, int $v):boolean {
        $bit = 1<<$v;
        return ($this->rows[$y] & $bit) > 0 || ($this->cols[$x] & $bit) > 0 || ($this->boxes[$this->boxIndex($y,$x)] & $bit) > 0;
    }

    public function toggleBit(int $y,int $x):boolean{
        $v = $this->puzzle[$y][$x];
        $bit = 1<<v;
        $r = ($this->rows[$y]&$bit) > 0;
        $this->rows[$y] ^= $bit;
        $this->cols[$x] ^= $bit;
        $this->boxes[$this->boxIndex($y,$x)] ^= $bit;
        return $r;
    }

    private function boxIndex(int $y,int $x):int {
        $boxY = (y/3);
        $boxX = (x/3);
        return boxY*3 + boxX;
    }

    public function throwIf(boolean $expr):void {
        if ($expr !== false) {
            throw new IllegalArgumentException();
        }
    }

    private function preValidate():boolean{
        if ($this->puzzle.length != SIZE){
            return false;
        }
        for ($row = 0;$row<SIZE;++$row){
            if (count($this->puzzle[$row])!=SIZE){
                return false;
            }
            for ($col = 0; $col < SIZE;++$col){
                if (!$this->inRange(MIN,MAX,$this->puzzle[$row][$col])){
                    return false;
                }
            }
        }
        return true;
    }

    public function inRange(int $min,int $max,int $v):boolean{
        return $v>=$min && $v<=$max;
    }

    private function populateCells():void{
        for ($row = 0;$row<SIZE;++$row){
            for ($col = 0; $col < SIZE;++$col){
                if ($this->puzzle[$row][$col]==MIN){
                    $this->emptyCells[]=new Cell($row,$col);
                }
            }
        }
    }

    private function goBackToFirstCellWithPossibleMove ():boolean {
        $picked = false;

        while (hasMore(checked)&&!picked){
            $cell = array_pop($this->checkedCells);

            $cell->setValue(0);
            $this->emptyCells[]=$cell;
            if ($cell->movesCount()>0 && $cell->canTrySomethingNew()){
                $picked=true;
            }else{
                $cell->resetTriedMoves();
                $cell->refresh();
            }
        }
        return $picked;
    }

    private function hasMore(array $l):boolean {
        return count($l) > 0;
    }

    private function cloneBoard ():array {
        $ret = array_slice($this->puzzle,0);

        for ($i = 0;$i<SIZE;++$i){
            $ret[$i] = array_slice($this->puzzle[$i],0);
        }
        return $ret;
    }

    public function findCell (int $row,int $col) {
        foreach ($this->emptyCells as $cell){
            if ($cell->row == $row && $cell->col == $col) {
                return cell;
            }
        }
        return null;
    }

    private function setupConnectedCells ():void {
        foreach ($this->empty as $cell){
            $cell->setupConnections();
        }
    }

    public function solve():array {
        $this->ntries = $this->iteration = 0;
        $solution = null;


        do {
            $this->sort();
            $this->iteration = 0;
            while ($this->hasMore($this->empty)){
                throwIf(++$this->iteration > 25000);
                $cell = $this->empty[0];

                if ($cell->pickMove()){
                  $this->checked[] = cell;
                  array_shift($this->empty);
                }else{
                    $this->goBackToFirstCellWithPossibleMove();

                }
                sort();
            }

        throwIf(!($solution == null || $this->equals($solution)));
        $solution = $this->cloneBoard();

        while (goBackToFirstCellWithPossibleMove()){};

        } while ($this->ntries++ < 1&&$this->iteration<5000);

        return solution;
    }

    private function equals (array $grid):boolean{
        if ($grid == null) {
            return false;
        }

        if (count($this->puzzle.length) != count($grid)){
            return false;
        }

        for ($i = 0,$n=count($this->puzzle);$i<$n;++$i){
            if (count($this->puzzle[$i])!=count($grid[i])){
                return false;
            }
            for ($j = 0,$m=count(puzzle[i]);$j<$m;++$j){
                if ($this->puzzle[$i][$j]!=$grid[$i][$j]){
                    return false;
                }
            }
        }
        return true;
        }
    public function sort():void {
        uasort($this->emptyCells, function ($a,$b) { return $a->movesCount - $b->movesCount;} );
    }
}