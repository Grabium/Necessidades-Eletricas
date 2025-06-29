<?php
//função de captura e contagem de entrada.
function getInput(int $qtd):array
{
  $input = fgets(STDIN);
  $inputArray = explode(" ", $input);
  $countInputArray = count($inputArray);
  
  if($countInputArray != $qtd){
    return die('Quantidade de argumentos esperados : '.$qtd.'.'.PHP_EOL.'Quantidade passada: '.$countInputArray.'.'.PHP_EOL );
  }
  
  foreach($inputArray as $input){
    $newInputArray[] = (int)$input;
  }
  
  return $newInputArray;
}


class Client
{
  //private Matriz $matriz;
  //private Query $query;
  
  private function factoryMatriz($metaDadosMatrizArray)
  {
    for($i = 1; $i <= $metaDadosMatrizArray[2]; $i++){
      $coordenadasFEArray[] = getInput(2);
    }
    
    $this->matriz = new Matriz($metaDadosMatrizArray, $coordenadasFEArray);
  }
  
  private function factoryQuery()
  {
    $quantidadeQueries = getInput(1)[0];
    $idABuscarArray = getInput($quantidadeQueries);
    $this->query = new Query($quantidadeQueries, $idABuscarArray);
  }
  
  private function encerrar($metaDadosMatrizArray)
  {
    $qtdl = $metaDadosMatrizArray[0];
    $qtdc = $metaDadosMatrizArray[1];
    $qtdfe = $metaDadosMatrizArray[2];
    
    if($qtdl==0 && $qtdc==0 && $qtdfe==0){
      return true;
    }
    
    return false;
  }
  
  public function run()
  {
    While(true){
      $metaDadosMatrizArray = getInput(3);
      if($this->encerrar($metaDadosMatrizArray)){
        die();
      }
      $this->factoryMatriz($metaDadosMatrizArray);
      $this->factoryQuery();
      $this->matriz->gerarArraysESetLinhasLivres();
      $this->query->find($this->matriz->arrayCelulas);
    }
  }
}

(new Client)->run();

class Matriz
{
  public function __construct($metaDadosMatrizArray, $coordenadasFEArray)
  {
    $this->quantidadeLinhas = $metaDadosMatrizArray[0];
    $this->quantidadeColunas = $metaDadosMatrizArray[1];
    $this->quantidadeFE = $metaDadosMatrizArray[2];
    $this->coordenadasFEArray = $coordenadasFEArray;
  }
  
  public gerarArraysESetLinhasLivres()
  {
    //
    $controleNumId = 1;
  }
}

class Query
{
  public function __construct($quantidadeQueries, $idABuscarArray)
  {
    $this->quantidadeQueries = (int)$quantidadeQueries;
    $this->idABuscarArray = $idABuscarArray;
    $this->idABuscarArray[$this->quantidadeQueries - 1] = (int)$idABuscarArray[$this->quantidadeQueries - 1];
  }
  
  public function find(Matriz $matriz)
  {
    //metodo 1 - buscar entre os arrays de prioriddes.
    //método 2 - se for após ou igual à última célula de prioridade.
    //método 3 - buscar ordem e dividir e considerar o resto da divisão. Buscar nas linhas, as células livres.
  }
}