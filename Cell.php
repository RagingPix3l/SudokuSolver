<?php


class Cell {
    const SIZE = 9;
    const MIN = 0;

    public int $row;
    public int $col;


    public array $moves = [];
    public array $triedMoves = [];
    public array $connected = [];

    public function __construct(int $pRow,int $pCol) {
        $this->row = $pRow;
        $this->col = $pCol;
        $this->refresh();
    }

    public function canTrySomethingNew ():boolean {
            return $this->moves[0] > MIN;
    }

    public function resetTriedMoves():void {

        for ($i = 1;$i<=SIZE;++$i){
            $this->triedMoves[$i] = 0;
        }

    }

    public function pickMove() : boolean{
            if ($this->moves[0]<=MIN){
                return false;
            }

            $i = SudokuSolver::$instance->ntries%2 == 1 ? 1 : SIZE;
            $dir = SudokuSolver::$instance->ntries%2 == 1 ? 1 : -1;


            for (;iSudokuSolver::$instance->inRange(1,SIZE,$i);$i+=$dir){
                if ($this->moves[$i] > MIN){
                    break;
                }
            }

            if (!SudokuSolver::$instance->inRange(1,MAX,$i)){
                return false;
            }

            setValue($i);
            return true;
        }

        public function setValue(int $v){
            if ($v == 0 && SudokuSolver::$instance->puzzle[$this->row][$this->col] > 0 ){
                $this->toggleBit($this->row,$this->col);
            }
            SudokuSolver::$instance->puzzle[$this->row][$this->col] = $v;

            if ($v>MIN){
                SudokuSolver::$instance->toggleBit($this->row,$this->col);
                SudokuSolver::$instance->throwIf($this->moves[$v]<=0);

                $this->moves[$v] = 0;
                $this->moves[0]--;
                $this->triedMoves[$v] = 1;

            }

            foreach ($this->connected as $cell){
                $cell->refresh();
            }
        }
        public function indexOf(array $c,$cell):int {
            foreach ($c as $k => $v){
                if ($c[$k] == $cell){
                    return  $k;
                }
            }
            return -1;
        }
        public function addConnection(int $pRow, int $pCol):void {
            if ($pRow == $this->row && $pCol == $this->col){
                return;
            }
            $cell = SudokuSolver::$instance->findCell (pRow,pCol);
                    if (indexOf($this->connected,$cell)>=0){
                        return;
                    }
                    $this->connected[]($cell);
                }

        public function setupConnections():void {

            for ($i = 0;$i<SIZE;++$i){
                $v = puzzle[$this->row][$i];
                if ($v==MIN){
                    $this->addConnection($this->row,$i);
                }
                $v = puzzle[$i][$this->col];
                if ($v==MIN){
                    addConnection($i,$this->col);
                }
            }

            $boxX = ($this->col/3)*3;
            $boxY = ($this->row/3)*3;

            for ($i = 0; $i < 3;++$i) {
                for ($j = 0; $j < 3; ++$j) {
                    $v = SudokuSolver::$instance->puzzle[$boxY + $i][$boxX + $j];
                    if ($v == MIN) {
                        $this->addConnection($boxY + $i, $boxX + $j);
                    }
                }
            }
        }

        public function refresh():void {

            $moves[0] = 0;
            for ($i = 1;i<=SIZE;++$i){
                if (SudokuSolver::$instance->sBitSet($this->row,$this->col,$i)||$this->triedMoves[$i]>0){
                    $this->moves[i] = 0;
                    continue;
                }
                $this->moves[$i] = 1;
                $this->moves[0] ++;
            }
        }


}