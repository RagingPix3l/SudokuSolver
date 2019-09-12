<?php

// original java code from my solution

/*
import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;
import java.util.Collections;
import java.util.Comparator;

public class SudokuSolver {

    final int [][] puzzle;
    final int MAX = 9;
    final int MIN = 0;
    final int SIZE = 9;

    final List <Cell> empty = new LinkedList<Cell>();
    final List <Cell> checked = new LinkedList<Cell>();

    final int [] rows = new int [SIZE];
    final int [] cols = new int [SIZE];
    final int [] boxes = new int [SIZE];

    int ntries;
    int iteration;

    public SudokuSolver(int[][] grid) {
        puzzle = grid;
        throwIf(!preValidate());
        initBits();
        populateCells();
        throwIf(!hasMore(empty));
        setupConnectedCells();
    }


    public int[][] solve() {
        ntries = iteration = 0;
        int [][] solution = null;
        Cell cell;

        do {
            sort();
            iteration = 0;
            while (hasMore(empty)){
                throwIf(++iteration > 25000);
                cell = empty.get(0);

                if (cell.pickMove()){
                  checked.add(cell);
                  empty.remove(cell);
                }else{
                   goBackToFirstCellWithPossibleMove();

                }
                sort();

            }

            throwIf(!(solution == null || equals(solution)));
            solution = cloneBoard();

            while (goBackToFirstCellWithPossibleMove()){};

        } while (ntries++ < 1&&iteration<5000);

        return solution;
    }

    public void sort() {
         Collections.sort(empty,Comparator.comparing(Cell::movesCount));

    }
    public void initBits() {
        for (int i = 0; i < SIZE;++i){
            for (int j = 0;j < SIZE; ++j){
                if (puzzle[i][j]>0){
                    throwIf(isBitSet(i,j,puzzle[i][j]));
                    toggleBit(i,j);
                }
            }
        }
    }

    public boolean isBitSet(final int y, final int x, final int v) {
        final int bit = 1<<v;
        return (rows[y] & bit) > 0 || (cols[x] & bit) > 0 || (boxes[boxIndex(y,x)] & bit) > 0;
    }

    public boolean toggleBit(final int y,final int x){
        final int v = puzzle[y][x];
        final int bit = 1<<v;
        boolean r = (rows[y]&bit) > 0;
        rows[y] ^= bit;
        cols[x] ^= bit;
        boxes[boxIndex(y,x)] ^= bit;
        return r;
    }

    public int boxIndex(final int y,final int x) {
        final int boxY = (y/3);
        final int boxX = (x/3);
        return boxY*3 + boxX;
    }

    private void throwIf(boolean expr) {
        if (expr) {
            throw new IllegalArgumentException();
        }
    }

    private int [][] cloneBoard () {
        int [][] ret = puzzle.clone();
        for (int i = 0;i<SIZE;++i){
            ret[i] = puzzle[i].clone();
        }
        return ret;
    }

    private boolean equals (int [][] grid){
        if (grid == null) {
            return false;
        }

        if (puzzle.length != grid.length){
            return false;
        }

        for (int i = 0,n=puzzle.length;i<n;++i){
            if (puzzle[i].length!=grid[i].length){
                return false;
            }
            for (int j = 0,m=puzzle[i].length;j<m;++j){
                if (puzzle[i][j]!=grid[i][j]){
                    return false;
                }
            }
        }
        return true;
    }

    private boolean goBackToFirstCellWithPossibleMove () {
        boolean picked = false;
        Cell cell;
        while (hasMore(checked)&&!picked){
            cell = checked.remove(checked.size()-1);
            cell.setValue(0);
            empty.add(cell);
            if (cell.movesCount()>0 && cell.canTrySomethingNew()){
                picked=true;
            }else{
                cell.resetTriedMoves();
                cell.refresh();
            }
        }
        return picked;
    }

    private String stringify() {
        StringBuilder sb = new StringBuilder();
        sb.append("{//0 1 2 3 4 5 6 7 8\n");
        for (int i = 0;i<SIZE;++i){
            sb.append("  {");
            for (int j = 0;j<SIZE;++j){
                if (j>0){
                    sb.append(",");
                }
                sb.append(puzzle[i][j]);
            }
            sb.append("}");
            if (i<SIZE-1){
              sb.append(",");
            }else{
              sb.append(" ");
            }
            sb.append("//" + i);
            sb.append("\n");
        }
        sb.append("\n};\n");
        return sb.toString();
    }

    private void setupConnectedCells () {
        for (Cell cell:empty){
            cell.setupConnections();
        }
    }

    private boolean hasMore(List l) {
        return l.size() > 0;
    }

    private boolean inRange(final int min,final int max,final int v) {
        return v>=min && v<=max;
    }

    private boolean preValidate(){
        if (puzzle.length != SIZE){
            return false;
        }
        for (int row = 0;row<SIZE;++row){
            if (puzzle[row].length!=SIZE){
                return false;
            }
            for (int col = 0; col < SIZE;++col){
                if (!inRange(MIN,MAX,puzzle[row][col])){
                    return false;
                }
            }
        }
        return true;
    }

    private void populateCells(){
        for (int row = 0;row<SIZE;++row){
            for (int col = 0; col < SIZE;++col){
                if (puzzle[row][col]==MIN){
                    empty.add(new Cell(row,col));
                }
            }
        }
    }

    private <T> void println (T p){
        System.out.println(String.valueOf(p));
    }

    private <T> void print(T p){
        System.out.print(String.valueOf(p));
    }

    private Cell findCell (final int row,final int col) {
        for (Cell cell:empty){
            if (cell.row == row && cell.col == col) {
                return cell;
            }
        }
        return null;
    }

    private class Cell {

        final int row;
        final int col;

        final int [] moves = new int [SIZE+1];
        final int [] triedMoves = new int [SIZE+1];
        final List <Cell> connected = new ArrayList<Cell>(27);

        public Cell(final int pRow,final int pCol) {
            row = pRow;
            col = pCol;
            refresh();
        }

        public boolean canTrySomethingNew () {
            return moves[0] > MIN;
        }

        public void resetTriedMoves() {

             for (int i = 1;i<=SIZE;++i){
                triedMoves[i] = 0;
             }

        }

        public boolean pickMove(){
            if (moves[0]<=MIN){
                return false;
            }

            int i = ntries%2 == 1 ? 1 : SIZE;
            final int dir = ntries%2 == 1 ? 1 : -1;


            for (;inRange(1,SIZE,i);i+=dir){
                if (moves[i] > MIN){
                    break;
                }
            }

            if (!inRange(1,MAX,i)){
                  return false;
            }

            setValue(i);
            return true;
        }

        public void setValue(final int v){
            if (v == 0 && puzzle[row][col] > 0 ){
                toggleBit(row,col);
            }
            puzzle[row][col] = v;

            if (v>MIN){
                toggleBit(row,col);
                throwIf(moves[v]<=0);

                moves[v] = 0;
                moves[0]--;
                triedMoves[v] = 1;

            }

            for (Cell cell:connected){
                cell.refresh();
            }
        }

        public void addConnection(final int pRow, final int pCol) {
            if (pRow == row && pCol == col){
                return;
            }
            final Cell cell = findCell (pRow,pCol);
            if (connected.indexOf(cell)>=0){
                return;
            }
            connected.add(cell);
        }

        public void setupConnections() {

            for (int i = 0;i<SIZE;++i){
                int v = puzzle[row][i];
                if (v==MIN){
                    addConnection(row,i);
                }
                v = puzzle[i][col];
                if (v==MIN){
                    addConnection(i,col);
                }
            }

            final int boxX = (col/3)*3;
            final int boxY = (row/3)*3;

            for (int i = 0; i < 3;++i){
                for (int j = 0; j<3;++j){
                    int v = puzzle[boxY+i][boxX+j];
                    if (v==MIN){
                        addConnection(boxY+i,boxX + j);
                    }
                }
            }
        }

        public void refresh() {

            moves[0] = 0;
            for (int i = 1;i<=SIZE;++i){
                if (isBitSet(row,col,i)||triedMoves[i]>0){
                  moves[i] = 0;
                  continue;
                }
                moves[i] = 1;
                moves[0] ++;
            }
        }

        void printOut () {
            String s = toString();
            println(s);
        }

        public String toString() {
            return String.format("Cell (%d,%d) possible %d moves: %s",row,col,movesCount(),movesToString());
        }

        int movesCount() {
            return moves[0];
        }

        String movesToString(){
            StringBuilder sb = new StringBuilder();
            for (int i = 1;i<=SIZE;++i){

              if (moves[i]>MIN){
                  if (sb.length() > 0){
                    sb.append(",");
                  }
                  sb.append(i);
              }
            }
            return sb.toString();
        }

    }

}
 */