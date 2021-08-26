<?php


class Player
{
    public int $balance;
    public string $name;
    public int $bet;

    function addName()
    {
        $this->name = readline('Please Enter your name: ') . PHP_EOL;
    }

    function __toString()
    {
        return $this->name . ' has balance : $' . $this->balance . PHP_EOL;
    }

    function addBalance()
    {
        $validate = true;
        while ($validate) {
            $balance = readline("Insert amount to add balance : ") . PHP_EOL;
            $balance = (int)$balance;
            if ($balance < 0) {
                echo "Invalid amount request" . PHP_EOL;
            } else {
                $validate = false;
            }
            $this->balance = $balance;
        }
    }

    function putBet(int $bet)
    {
        $this->balance -= $bet;
    }

    function wonBet(int $winnings)
    {
        $this->balance += $winnings;
    }

}

class SlotMachine
{
    public string $name;
    public array $betCoefs;
    public array $symbols;
    public array $symbolsLineValue;
    public array $spinResult;
    public array $definedWins;
    public int $bet;
    public int $rows = 3;
    public int $columns = 3;

    function __construct(string $name, array $betCoefs, array $symbols, array $symbolsLineValue, array $definedWins = [])
    {
        $this->name = $name;
        $this->betCoefs = $betCoefs;
        $this->symbols = $symbols;
        $this->symbolsLineValue = $symbolsLineValue;
        $this->definedWins = $definedWins;
    }

    function __toString()
    {
        return $this->name;
    }

    function generateSpin()
    {
        foreach (range(1, $this->rows) as $row) {
            foreach (range(1, $this->columns) as $column) {
                $this->spinResult[$row][$column] = $this->symbols[array_rand($this->symbols)];
            }
        }
    }

    function setRowsColumns(int $rows, int $columns)
    {
        $this->rows = $rows;
        $this->columns = $columns;
    }

    function displaySpin()
    {
        foreach ($this->spinResult as $key => $row) {
            echo PHP_EOL . str_repeat("----", count($row)) . PHP_EOL;
            foreach ($this->spinResult[$key] as $symbol) {
                echo " $symbol |";
            }

        }
        echo PHP_EOL . str_repeat("----", count($this->spinResult[1])) . PHP_EOL;
    }

    function checkForWin(Player $gambler)
    {
        $winSum = 0;

        // Līniju pārbaude - iztur  laukuma izmaiņas
        foreach ($this->spinResult as $row) {
            $winSum += $this->calculateWinAmount($gambler, $row);

        }
        // Diagonāļu pārbaude
        if ($this->columns === $this->rows && $this->rows % 2 === 1) {
            $topToBottomDiagonal = [];
            $bottomToTopDiagonal = [];
            $backwardsCounter = $this->rows;
            foreach (range(1, count($this->spinResult)) as $key) {
                array_push($bottomToTopDiagonal, $this->spinResult[$key][$backwardsCounter]);
                $backwardsCounter--;
                array_push($topToBottomDiagonal, $this->spinResult[$key][$key]);
            }
            $winSum += $this->calculateWinAmount($gambler, $topToBottomDiagonal);
            $winSum += $this->calculateWinAmount($gambler, $bottomToTopDiagonal);

        }
        // Lai veiktu citas uzvaras pārbaudes jāveido jauns masīvs ar uzvarošo elementu atrašanās vietas koordinātēm
        // kas ir jānodefinē un jāievada slotmachine izveidošanas brīdī, bet tie nespēj pielāgoties laukuma maiņām
        // [[[1,2],[2,2],[3,2]],[[1,1],[2,1],[3,1]],[[1,3],[2,3],[3,3]]] - kollonnas pie 3x3 laukuma
        if (count($this->definedWins) > 0) {
            foreach ($this->definedWins as $win) {
                $storedWin = [];
                foreach ($win as $keyIndexes) {
                    array_push($storedWin, $this->spinResult[$keyIndexes[0]][$keyIndexes[1]]);
                }
                $winSum += $this->calculateWinAmount($gambler, $storedWin);
            }
        }
        echo "Won : $$winSum " . PHP_EOL;


    }

    function calculateWinAmount(Player $gambler, array $winSymbols): int
    {
        $win = 0;
        if (count(array_unique($winSymbols)) === 1) {
            $winElement = $winSymbols[1];
            $lineValue = $this->symbolsLineValue[$winElement];
            $win = $lineValue * array_search($this->bet, $this->betCoefs);
            $gambler->wonBet($win);
        }
        return $win;
    }

    function putBet(Player $gambler)
    {
        $bet = 0;
        $validate = true;
        while ($validate) {
            $betSizes = implode(' ', $this->betCoefs);
            $bet = readline("Choose bet size ($betSizes): ") . PHP_EOL;
            $bet = (int)$bet;
            if ($gambler->balance < $bet) {
                echo "Not enough in your balance, you have left : $gambler->balance" . PHP_EOL;
                $input = readline("Enter 'exit' to Rage quit or 'add' to put more in balance : ");
                if ($input === 'exit') {
                    echo 'Until next time ' . PHP_EOL;
                    exit;
                } elseif ($input === 'add') {
                    $gambler->addBalance();
                } else {
                    echo "You were thrown out ! " . PHP_EOL;
                    exit;
                }
            } elseif (in_array($bet, $this->betCoefs)) {
                $validate = false;
            } else {
                echo "Invalid input try again" . PHP_EOL;
            }
        }
        $this->bet = $bet;
        $gambler->putBet($this->bet);
    }

    function oneMore(): bool
    {
        echo "Enter 'again' for one more spin or anything else to get to lobby : " . PHP_EOL;
        return readline('>') === 'again';
    }
}

class Fenikss
{
    public string $name;
    public array $machines;

    function __construct(string $name)
    {
        $this->name = $name;
    }

    function __toString(): string
    {
        return "Welcome to $$this->name ! " . PHP_EOL . 'Enjoy your time.' . PHP_EOL . PHP_EOL;
    }

    function addSlotMachines(array $machines)
    {
        $this->machines = $machines;
    }

    function chooseMachine(): SlotMachine
    {
        echo 'Available are ' . count($this->machines) . ' slot machines. Please choose!' . PHP_EOL;
        echo "Enter 'exit' to go home " . PHP_EOL;
        foreach ($this->machines as $key => $machine) {
            $key = $key + 1;
            echo "Enter $key to play $machine" . PHP_EOL;
        }
        $validate = true;
        while ($validate) {
            $choose = readline(">");
            if ($choose === 'exit') {
                echo 'See you next time in ' . $this->name . PHP_EOL;
                exit;
            }
            $choose = (int)$choose;
            if (isset($this->machines[$choose - 1])) {
                $validate = false;
            } else {
                echo "invalid choice, choose again" . PHP_EOL;
            }
        }
        return $this->machines[$choose - 1];
    }

    function newPlayer(): Player
    {
        $gambler = new Player();
        $gambler->addName();
        $gambler->addBalance();
        return $gambler;
    }
}

$betKoeficenti = [1 => 10, 2 => 20, 3 => 40, 4 => 80, 5 => 160];
$definedWinsforColumns3x3 = [[[1, 2], [2, 2], [3, 2]], [[1, 1], [2, 1], [3, 1]], [[1, 3], [2, 3], [3, 3]]];
$definedWinsforBoard3x4= [[[1, 1], [2, 2], [3, 3],[3, 4]], [[3, 1], [2, 2], [1, 3],[1, 4]], [[1, 1], [1, 2], [2, 3],[3, 4]], [[3, 1], [3, 2], [2, 3],[1, 4]]];


$symbolsForAlphabet = ['A', 'B', 'D', 'E'];
$alphabetLineValues = ['A' => 15, 'B' => 10, 'C' => 5, 'D' => 20, 'E' => 100];
$Alphabet = new SlotMachine("Letter Spiner ABDCD ", $betKoeficenti, $symbolsForAlphabet, $alphabetLineValues, $definedWinsforBoard3x4);
$Alphabet->setRowsColumns(3, 4);

$symbolsForDigit = ['1', '2', '3', '4', '5'];
$digitLineValues = ['1' => 25, '2' => 10, '3' => 25, '4' => 15, '5' => 15];
$Digit = new SlotMachine('DigitMaster 12345', $betKoeficenti, $symbolsForDigit, $digitLineValues,$definedWinsforBoard3x4);
$Digit->setRowsColumns(3, 4);

$symbolsForRandom = ['@', '!', '*', '%', '$'];
$randomLineValues = ['@' => 55, '!' => 100, '*' => 20, '%' => 10, '$' => 10];
$Random = new SlotMachine('Random character finder $%!@&', $betKoeficenti, $symbolsForRandom, $randomLineValues,$definedWinsforBoard3x4);
$Random->setRowsColumns(3, 4);

$slotMachines = [$Alphabet, $Digit, $Random];

$ImantaFenikss = new Fenikss(' Fenikss Imanta ');
$ImantaFenikss->addSlotMachines($slotMachines);

echo $ImantaFenikss;
$gambler = $ImantaFenikss->newPlayer();
$inFenikss = true;
while ($inFenikss) {
    $machine = $ImantaFenikss->chooseMachine();

    $play = true;
    echo 'Welcome to ' . $machine . PHP_EOL;
    while ($play) {
        echo $gambler;
        $machine->putBet($gambler);
        $machine->generateSpin();
        $machine->displaySpin();
        $machine->checkForWin($gambler);
        $play = $machine->oneMore();
    }
}






