<?php


function testTime(bool $fimDoScript = false)
{
  // 1. Marca o tempo de início
  static $tempo_inicio = microtime(true);
  
  if($fimDoScript){
    // 2. Marca o tempo de fim da execução do script
    $tempo_fim = microtime(true);
  
    // 3. Calcula o tempo decorrido.
    $tempo_decorrido = ($tempo_fim - $tempo_inicio);
    
    echo "Tempo de execução do script: " . round($tempo_decorrido, 4) . " segundos.\n";
  }
  
}

//função de captura e contagem de entrada.
function getInput(int $qtd, int $linhaQueInvovcou):array
{
  $input = fgets(STDIN);
  $inputArray = explode(" ", $input);
  $countInputArray = count($inputArray);
  
  if($countInputArray != $qtd){
    return die('Quantidade de argumentos esperados : '.$qtd.'.'.PHP_EOL.'Quantidade passada: '.$countInputArray.' na linha '.$linhaQueInvovcou.'.'.PHP_EOL );
  }
  
  foreach($inputArray as $input){
    $newInputArray[] = (int)$input;
  }
  
  return $newInputArray;
}


class Client
{
  private Matriz $matriz;
  private Query $query;
  
  private function factoryMatriz($metaDadosMatrizArray)
  {
    for($i = 1; $i <= $metaDadosMatrizArray[2]; $i++){
      $coordenadasFEArray[] = getInput(2, __LINE__);
    }
    
    $this->matriz = new Matriz($metaDadosMatrizArray, $coordenadasFEArray);
  }
  
  private function factoryQuery()
  {
    $quantidadeQueries = getInput(1, __LINE__)[0];
    $idABuscarArray = getInput($quantidadeQueries, __LINE__);
    $this->query = new Query($quantidadeQueries, $idABuscarArray);
  }
  
  private function encerrar($metaDadosMatrizArray):bool
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
      $metaDadosMatrizArray = getInput(3, __LINE__);
      if($this->encerrar($metaDadosMatrizArray)){
        testTime(true);
        die();
      }
      $this->factoryMatriz($metaDadosMatrizArray);
      $this->factoryQuery();
      $this->matriz->gerarArraysESetLinhasLivres();
      $this->query->find($this->matriz);
    }
  }
}

(new Client)->run();

class Matriz
{
  public int $quantidadeLinhas;
  public int $quantidadeColunas ;
  public int $quantidadeFE;
  public array $coordenadasFEArray;
  public int $controleNumId;
  public array $prioridade1Array;
  public array $prioridade2Array;
  public array $coordenadasFE;
  public array $celulasIdentificadas;
  public array $linhasOcupadasEQuantidade;
  public array $coordenadasOcupadasTotal;

  public function __construct(array $metaDadosMatrizArray, array $coordenadasFEArray)
  {
    $this->quantidadeLinhas = $metaDadosMatrizArray[0];
    $this->quantidadeColunas = $metaDadosMatrizArray[1];
    $this->quantidadeFE = $metaDadosMatrizArray[2];
    $this->coordenadasFEArray = $coordenadasFEArray;//item = [linha,coluna]
    $this->controleNumId = 1;
    $this->prioridade1Array = [];
    $this->prioridade2Array = [];
    
  }
  
  public function gerarArraysESetLinhasLivres()
  {

    $this->setPrioridadesArrays();
  }
  
  private function setPrioridadesArrays()
  {
    $alteracoesPrioridade1RelatiavasAFE = array(
      [-1,-1],[-1,0],[-1,1],//noroeste, norte, nordeste
      [0,-1],[0,1],//oeste, leste
      [1,-1],[1,0],[1,1]//sudoeste, sul, suldeste
    );
    
    $alteracoesPrioridade2RelatiavasAFE = array(
      [-2,-2],[-2,-1],[-2,0],[-2,1],[-2,2],//noroeste, norte, nordeste
      [-1,-2],[-1,2],
      [0,-2],[0,2],//oeste, leste
      [1,-2],[1,2],
      [2,-2],[2,-1],[2,0],[2,1],[2,2]
    );
    
    
    for($fe = 0; $fe < $this->quantidadeFE; $fe++){
      $this->coordenadasFE = $this->coordenadasFEArray[$fe];
      $this->inserirEmLinhasOcupadasEQuantidade($this->coordenadasFE);
      $this->prioridade1Array = array_merge($this->prioridade1Array, array_map([$this, 'setCelInArrayPriority'], $alteracoesPrioridade1RelatiavasAFE));
      $this->prioridade2Array = array_merge($this->prioridade2Array, array_map([$this, 'setCelInArrayPriority'], $alteracoesPrioridade2RelatiavasAFE));
    }
    
    //remover elementos de valores NULL.
    $this->prioridade1Array = array_values(array_filter($this->prioridade1Array));
    //$this->prioridade2Array = $this->prioridade2Array);
    
    //dando prioridade à lista de prioridade 1. (array_map)
    //removendo itens NULL. (filter)
    //removendo duplicidades dentro da própria lista. (array_unique)
    $this->prioridade2Array = array_values(array_unique(array_filter(array_map(function($coordenada){
      if(!in_array($coordenada, $this->prioridade1Array)){
        return $coordenada;
      }
    },$this->prioridade2Array)), SORT_REGULAR));
    
    $this->prioridade1Array = $this->sortPriority($this->prioridade1Array);
    $this->prioridade2Array = $this->sortPriority($this->prioridade2Array);
    
    
    foreach(array_merge($this->prioridade1Array,$this->prioridade2Array) as $key => $coordenada){
      $this->celulasIdentificadas[$key +1] = $coordenada;
      $this->inserirEmLinhasOcupadasEQuantidade($coordenada);
    }

    $this->linhasOcupadasEQuantidade = array_count_values($this->linhasOcupadasEQuantidade);
  }

  private function inserirEmLinhasOcupadasEQuantidade(array $coordenada)
  {
    $this->coordenadasOcupadasTotal[] = $coordenada;
    $this->linhasOcupadasEQuantidade[] = $coordenada[0];
  }
  
  private function setCelInArrayPriority(array $alteracao)
  {
    $coordenadasDaCelula[0] = $this->coordenadasFE[0] + $alteracao[0];
    $coordenadasDaCelula[1] = $this->coordenadasFE[1] + $alteracao[1];
    
    if($this->verifyDuplicity($coordenadasDaCelula)){
      return;
    }
    
    if($this->ifCoordenateIsSameOtherFE($coordenadasDaCelula)){
      return;
    }
    
    if($this->isCoordenateOffSet($coordenadasDaCelula)){
      return;
    }
    
    return $coordenadasDaCelula;
  }
  
  private function verifyDuplicity(array $coordenadasDaCelula):bool
  {
    if(in_array($coordenadasDaCelula, $this->prioridade1Array)){
      return true;
    }
    
    return false;
  }
  
  private function ifCoordenateIsSameOtherFE(array $coordenadasDaCelula):bool
  {
    if(in_array($coordenadasDaCelula, $this->coordenadasFEArray)){
      return true;
    }
    return false;
  }
  
  private function isCoordenateOffSet(array $coordenadasDaCelula):bool
  {
    if($coordenadasDaCelula[0] < 1){
      return true;
    }
    
    if($coordenadasDaCelula[1] < 1){
      return true;
    }
    
    if($coordenadasDaCelula[1] > $this->quantidadeColunas){
      return true;
    }
    
    if($coordenadasDaCelula[0] > $this->quantidadeLinhas){
      return true;
    }
    
    return false;
  }
  
  private function sortPriority(array $arrayPriority)
  {
    usort($arrayPriority, function($a, $b) {
      // Primeiro, compara as linhas
      if ($a[0] < $b[0]) {
          return -1; // $a deve vir antes de $b
      }
      if ($a[0] > $b[0]) {
          return 1;  // $a deve vir depois de $b
      }
  
      // Se as linhas são iguais, compara as colunas
      if ($a[1] < $b[1]) {
          return -1; // $a deve vir antes de $b
      }
      if ($a[1] > $b[1]) {
          return 1;  // $a deve vir depois de $b
      }
  
      // Se linhas e colunas são iguais, a ordem não importa
      return 0;
    });
    
    return $arrayPriority;
  }
}

class Query
{
  public int $quantidadeQueries;
  public array $idABuscarArray;
  public Matriz $matriz;

  public function __construct(int $quantidadeQueries, array $idABuscarArray)
  {
    $this->quantidadeQueries = $quantidadeQueries;
    $this->idABuscarArray = $idABuscarArray;
  }
  
  private function imprimirResposta(array $coordenada)
  {
    echo 'RESULTADO: '.$coordenada[0].' '.$coordenada[1].PHP_EOL.PHP_EOL;
  }
  
  private function localizarNumaMatrizComoPadrao($idABuscar)
  {
    $linha = intdiv($idABuscar, $this->matriz->quantidadeColunas);
    $acrescimoEmProximaLinha = ($idABuscar % $this->matriz->quantidadeColunas);
    
    $linha = ($acrescimoEmProximaLinha > 0) ? $linha + 1 : $linha ;
    $coluna = ($acrescimoEmProximaLinha > 0) ? $acrescimoEmProximaLinha : $this->matriz->quantidadeColunas;
    return [$linha, $coluna];
  }
  
  public function find(Matriz $matriz)
  {
    //$respostaArray;
    $this->matriz = $matriz;
    //metodo 1 - buscar entre os arrays de prioriddes.
    //método 2 - se for após ou igual à última célula de prioridade.
    //método 3 - buscar ordem e dividir e considerar o resto da divisão. Buscar nas linhas, as células livres.
    foreach($this->idABuscarArray as $idABuscar){
      echo 'Buacando ID: '.$idABuscar.PHP_EOL;
      //caso já esteja indexado com célula de prioridade.
      if(isset($this->matriz->celulasIdentificadas[$idABuscar])){
        echo $idABuscar. ' - Metodo 1.'.PHP_EOL;
        $this->imprimirResposta($this->matriz->celulasIdentificadas[$idABuscar]);
        continue;
      }
      
      //caso deva estar em uma célula APÓS TODAs as células de prioridade
      if($this->seBuscadoEAposCelulasIdentificadas($idABuscar)){
        echo $idABuscar. ' - Metodo 2.'.PHP_EOL;
        $coordenada = $this->localizarNumaMatrizComoPadrao($idABuscar + $this->matriz->quantidadeFE);
        $this->imprimirResposta($coordenada);
        continue;
      }

      echo $idABuscar. ' - Metodo 3.'.PHP_EOL;

      $celulasAConsumirAteAID = ($idABuscar - count($this->matriz->celulasIdentificadas));
      echo 'celulas a consumir: '.$celulasAConsumirAteAID.PHP_EOL;//die();
      $saltoDeLinhas = intdiv($celulasAConsumirAteAID, $this->matriz->quantidadeColunas);
      echo 'salto de linhas: '.$saltoDeLinhas.PHP_EOL;//die();
      $resto = ($celulasAConsumirAteAID % $this->matriz->quantidadeColunas);
      echo 'resto: '.$resto.PHP_EOL;//die();
      $linhasLivres = (min(array_keys($this->matriz->linhasOcupadasEQuantidade)) -1);
      
      
      //menor celula prioridade mudar isso para fora do loop
      foreach($this->matriz->celulasIdentificadas as $k => $celulaAtual){
        if($k == 1){
          $menorPrioridade = $celulaAtual[0].$celulaAtual[1];
          continue;
        }
        $celulaAtual = $celulaAtual[0].$celulaAtual[1];
        if($celulaAtual < $menorPrioridade){
          $menorPrioridade = $celulaAtual;
        }
      }
      
      //se antes da menor celula prioridade
      if(($menorPrioridade - 11)>= $celulasAConsumirAteAID){
        
        echo 'dentro das linhas antes de alterações.'.PHP_EOL;
        if($resto != 0){
          $coordenada = [$saltoDeLinhas +1, $resto];
        }else{
          $coordenada = [$saltoDeLinhas, $this->matriz->quantidadeColunas];
        }
        $this->imprimirResposta($coordenada);
        continue;
      }
      
      //entre linhas consumidas pela prioridade e FE.
      echo'localizado na zona de consumo.'.PHP_EOL;
      //quantidade de linhas livres
      $quantidadeDeLinhasLivresNaZonaAnterior =  ($menorPrioridade[0]-1);
      $celulasAConsumirAteAID  = $celulasAConsumirAteAID - ($quantidadeDeLinhasLivresNaZonaAnterior * $this->matriz->quantidadeColunas);
      echo 'celulas a consumir na zona de consumo: '.$celulasAConsumirAteAID.PHP_EOL;
      $linhaAtual = $menorPrioridade[0];
      
      echo 'Iterando: '.PHP_EOL.PHP_EOL.PHP_EOL;
      //loop
      while(true){
        echo PHP_EOL.'Linha: '.$linhaAtual.PHP_EOL;
        $celulasLivresNestaLinhaAtual = ($this->matriz->quantidadeColunas - $this->matriz->linhasOcupadasEQuantidade[$linhaAtual]);
        echo 'celulas livres nesta linha: '.$celulasLivresNestaLinhaAtual.PHP_EOL;
        $celulasAConsumirAteAID = ($celulasAConsumirAteAID - $celulasLivresNestaLinhaAtual);
        echo 'Consumindo...'.PHP_EOL.'restam ' . $celulasAConsumirAteAID . ' células.'.PHP_EOL;
        
        if($celulasAConsumirAteAID <=1){
          echo  'Achou: '.$celulasAConsumirAteAID.PHP_EOL;
          //se achou ==1, setar a linha e a primeira célula.
          $quantidadeDeCelulaLivreNestaLinhaQueSeraColunaPelaOrdem = $celulasLivresNestaLinhaAtual + $celulasAConsumirAteAID;
          echo 'A coluna será a '.$quantidadeDeCelulaLivreNestaLinhaQueSeraColunaPelaOrdem.'ª célula livre desta linha.'.PHP_EOL;
          //$coluna = ($this->matriz->quantidadeColunas + $celulasAConsumirAteAID);
          //$coordenada = [$linhaAtual, $coluna];
          //$this->imprimirResposta($coordenada);
          //var_dump($this->matriz->coordenadasOcupadasTotal);
          $colunasOcupadasNestaLinha = [];
          foreach($this->matriz->coordenadasOcupadasTotal as $coordenadaOcupada){
            if($coordenadaOcupada[0] != $linhaAtual){
              continue;
            }
            
            $colunasOcupadasNestaLinha[] = $coordenadaOcupada[1];
          }
          
          var_dump($quantidadeDeCelulaLivreNestaLinhaQueSeraColunaPelaOrdem);
          
          //se coluna buscada está antes da primeira coluna consumida
          if(min($colunasOcupadasNestaLinha) > 1){
            $coluna = $quantidadeDeCelulaLivreNestaLinhaQueSeraColunaPelaOrdem;
          }
          
          //se coluna buscada está depois da última coluna consumida
          if(max($colunasOcupadasNestaLinha) < $this->matriz->quantidadeColunas){
            $coluna = (max($colunasOcupadasNestaLinha) + $quantidadeDeCelulaLivreNestaLinhaQueSeraColunaPelaOrdem);
          }
          
          
          
          //pode haver um caso em que as FE estejam separadas e possibilite a busca entre células livres entre elas
          //caso não implementado...
          //seguindo em frente...
          $coordenada = [$linhaAtual, $coluna];
          $this->imprimirResposta($coordenada);
          //die();
          break;
        }
        
        $linhaAtual++;
      }
      
    }

    echo '-'.PHP_EOL;
  }


  //retorna true para ativar o metodo 2 ou false para ativar o metodo 3
  private function seBuscadoEAposCelulasIdentificadas(int $idABuscar):bool
  {
    $localizacaoPadrao = $this->localizarNumaMatrizComoPadrao(($idABuscar + $this->matriz->quantidadeFE));
    $ultimaPrioridade = $this->matriz->celulasIdentificadas[count($this->matriz->celulasIdentificadas)];
    
    //quando a coluna é 10 ($localizacaoPadrao[1] == 10)
    if($localizacaoPadrao[1] == 10){
      $localizacaoPadrao[1] = 0;
      $localizacaoPadrao[0] = ($localizacaoPadrao[0]+1);
    }
    
    //stringficação
    $localizacaoPadrao = $localizacaoPadrao[0].$localizacaoPadrao[1];
    $localizacaoPadrao = (int)$localizacaoPadrao;
    $ultimaPrioridade = $ultimaPrioridade[0].$ultimaPrioridade[1];
    $ultimaPrioridade = (int)$ultimaPrioridade;
    
    echo 'padrão: '.$localizacaoPadrao.' - ultima prioridade: '.$ultimaPrioridade.PHP_EOL;
    //true se vuscado for depois
    if($localizacaoPadrao <= $ultimaPrioridade){
      return false;
    }
    
    return true;   //buscado é depois.
  }
  
  

}

