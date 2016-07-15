<?php
  namespace Atua;

  class FPDF extends \fpdf\FPDF
  {
    // #################################################
    // # http://www.fpdf.org/en/script/script35.php    #
    // #################################################

    function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
    {
      $k = $this->k;
      $hp = $this->h;
      if($style=='F')
        $op='f';
      elseif($style=='FD' || $style=='DF')
        $op='B';
      else
        $op='S';
      $MyArc = 4/3 * (sqrt(2) - 1);
      $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

      $xc = $x+$w-$r;
      $yc = $y+$r;
      $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
      if (strpos($corners, '2')===false)
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
      else
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

      $xc = $x+$w-$r;
      $yc = $y+$h-$r;
      $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
      if (strpos($corners, '3')===false)
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
      else
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

      $xc = $x+$r;
      $yc = $y+$h-$r;
      $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
      if (strpos($corners, '4')===false)
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
      else
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

      $xc = $x+$r ;
      $yc = $y+$r;
      $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
      if (strpos($corners, '1')===false)
      {
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
        $this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k ));
      }
      else
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
      $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
      $h = $this->h;
      $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    // ###################################################
    // # Atua                                            #
    // ###################################################


  //Fun��o criada para contar quantas linhas a MultiCell ir� escrever.
    function MultiCellCount($w,$h,$txt,$border=0,$align='J',$fill=0)
    {

      $mCountCell = 0;

      //Output text with automatic or explicit line breaks
      $cw=&$this->CurrentFont['cw'];
      if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
      $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
      $s=str_replace("\r",'',$txt);
      $nb=strlen($s);
      if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
      $b=0;
      if($border)
      {
        if($border==1)
        {
          $border='LTRB';
          $b='LRT';
          $b2='LR';
        }
        else
        {
          $b2='';
          if(is_int(strpos($border,'L')))
            $b2.='L';
          if(is_int(strpos($border,'R')))
            $b2.='R';
          $b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
        }
      }
      $sep=-1;
      $i=0;
      $j=0;
      $l=0;
      $ns=0;
      $nl=1;
      while($i<$nb)
      {
        //Get next character
        $c=$s{$i};
        if($c=="\n")
        {
          //Explicit line break
          if($this->ws>0)
          {
            $this->ws=0;
            $this->_out('0 Tw');
          }
          $mCountCell++;
          #$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
          $i++;
          $sep=-1;
          $j=$i;
          $l=0;
          $ns=0;
          $nl++;
          if($border and $nl==2)
            $b=$b2;
          continue;
        }
        if($c==' ')
        {
          $sep=$i;
          $ls=$l;
          $ns++;
        }
        $l+=$cw[$c];
        if($l>$wmax)
        {
          //Automatic line break
          if($sep==-1)
          {
            if($i==$j)
              $i++;
            if($this->ws>0)
            {
              $this->ws=0;
              $this->_out('0 Tw');
            }
            $mCountCell++;
            #$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
          }
          else
          {
            if($align=='J')
            {
              $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
              $this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
            }
            $mCountCell++;
            #$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
            $i=$sep+1;
          }
          $sep=-1;
          $j=$i;
          $l=0;
          $ns=0;
          $nl++;
          if($border and $nl==2)
            $b=$b2;
        }
        else
          $i++;
      }
      //Last chunk
      if($this->ws>0)
      {
        $this->ws=0;
        $this->_out('0 Tw');
      }
      if($border and is_int(strpos($border,'B')))
        $b.='B';
      $mCountCell++;
      #$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
      $this->x=$this->lMargin;

      return $mCountCell;

    }

    // #################################################
    // # http://www.fpdf.org/en/script/script88.php    #
    // #################################################

    function PDF_Code128()
    {
      /*******************************************************************************
       * Script :  PDF_Code128
       * Version : 1.0
       * Date :    20/05/2008
       * Auteur :  Roland Gautier
       *
       * Code128($x, $y, $code, $w, $h)
       *     $x,$y :     angle sup�rieur gauche du code � barre
       *     $code :     le code � cr�er
       *     $w :        largeur hors tout du code dans l'unit� courante
       *                 (pr�voir 5 � 15 mm de blanc � droite et � gauche)
       *     $h :        hauteur hors tout du code dans l'unit� courante
       *
       * Commutation des jeux ABC automatique et optimis�e.
       *******************************************************************************/

      $this->T128    = array();
      $this->ABCset  = "";
      $this->Aset    = "";
      $this->Bset    = "";
      $this->Cset    = "";
      $this->SetFrom = array();
      $this->SetTo   = array();
      $this->JStart  = array("A" => 103, "B" => 104, "C" => 105);
      $this->JSwap   = array("A" => 101, "B" => 100, "C" => 99);

      $this->T128[] = array(2, 1, 2, 2, 2, 2);           //0   : [ ]
      $this->T128[] = array(2, 2, 2, 1, 2, 2);           //1   : [!]
      $this->T128[] = array(2, 2, 2, 2, 2, 1);           //2   : ["]
      $this->T128[] = array(1, 2, 1, 2, 2, 3);           //3   : [#]
      $this->T128[] = array(1, 2, 1, 3, 2, 2);           //4   : [$]
      $this->T128[] = array(1, 3, 1, 2, 2, 2);           //5   : [%]
      $this->T128[] = array(1, 2, 2, 2, 1, 3);           //6   : [&]
      $this->T128[] = array(1, 2, 2, 3, 1, 2);           //7   : [']
      $this->T128[] = array(1, 3, 2, 2, 1, 2);           //8   : [(]
      $this->T128[] = array(2, 2, 1, 2, 1, 3);           //9   : [)]
      $this->T128[] = array(2, 2, 1, 3, 1, 2);           //10  : [*]
      $this->T128[] = array(2, 3, 1, 2, 1, 2);           //11  : [+]
      $this->T128[] = array(1, 1, 2, 2, 3, 2);           //12  : [,]
      $this->T128[] = array(1, 2, 2, 1, 3, 2);           //13  : [-]
      $this->T128[] = array(1, 2, 2, 2, 3, 1);           //14  : [.]
      $this->T128[] = array(1, 1, 3, 2, 2, 2);           //15  : [/]
      $this->T128[] = array(1, 2, 3, 1, 2, 2);           //16  : [0]
      $this->T128[] = array(1, 2, 3, 2, 2, 1);           //17  : [1]
      $this->T128[] = array(2, 2, 3, 2, 1, 1);           //18  : [2]
      $this->T128[] = array(2, 2, 1, 1, 3, 2);           //19  : [3]
      $this->T128[] = array(2, 2, 1, 2, 3, 1);           //20  : [4]
      $this->T128[] = array(2, 1, 3, 2, 1, 2);           //21  : [5]
      $this->T128[] = array(2, 2, 3, 1, 1, 2);           //22  : [6]
      $this->T128[] = array(3, 1, 2, 1, 3, 1);           //23  : [7]
      $this->T128[] = array(3, 1, 1, 2, 2, 2);           //24  : [8]
      $this->T128[] = array(3, 2, 1, 1, 2, 2);           //25  : [9]
      $this->T128[] = array(3, 2, 1, 2, 2, 1);           //26  : [:]
      $this->T128[] = array(3, 1, 2, 2, 1, 2);           //27  : [;]
      $this->T128[] = array(3, 2, 2, 1, 1, 2);           //28  : [<]
      $this->T128[] = array(3, 2, 2, 2, 1, 1);           //29  : [=]
      $this->T128[] = array(2, 1, 2, 1, 2, 3);           //30  : [>]
      $this->T128[] = array(2, 1, 2, 3, 2, 1);           //31  : [?]
      $this->T128[] = array(2, 3, 2, 1, 2, 1);           //32  : [@]
      $this->T128[] = array(1, 1, 1, 3, 2, 3);           //33  : [A]
      $this->T128[] = array(1, 3, 1, 1, 2, 3);           //34  : [B]
      $this->T128[] = array(1, 3, 1, 3, 2, 1);           //35  : [C]
      $this->T128[] = array(1, 1, 2, 3, 1, 3);           //36  : [D]
      $this->T128[] = array(1, 3, 2, 1, 1, 3);           //37  : [E]
      $this->T128[] = array(1, 3, 2, 3, 1, 1);           //38  : [F]
      $this->T128[] = array(2, 1, 1, 3, 1, 3);           //39  : [G]
      $this->T128[] = array(2, 3, 1, 1, 1, 3);           //40  : [H]
      $this->T128[] = array(2, 3, 1, 3, 1, 1);           //41  : [I]
      $this->T128[] = array(1, 1, 2, 1, 3, 3);           //42  : [J]
      $this->T128[] = array(1, 1, 2, 3, 3, 1);           //43  : [K]
      $this->T128[] = array(1, 3, 2, 1, 3, 1);           //44  : [L]
      $this->T128[] = array(1, 1, 3, 1, 2, 3);           //45  : [M]
      $this->T128[] = array(1, 1, 3, 3, 2, 1);           //46  : [N]
      $this->T128[] = array(1, 3, 3, 1, 2, 1);           //47  : [O]
      $this->T128[] = array(3, 1, 3, 1, 2, 1);           //48  : [P]
      $this->T128[] = array(2, 1, 1, 3, 3, 1);           //49  : [Q]
      $this->T128[] = array(2, 3, 1, 1, 3, 1);           //50  : [R]
      $this->T128[] = array(2, 1, 3, 1, 1, 3);           //51  : [S]
      $this->T128[] = array(2, 1, 3, 3, 1, 1);           //52  : [T]
      $this->T128[] = array(2, 1, 3, 1, 3, 1);           //53  : [U]
      $this->T128[] = array(3, 1, 1, 1, 2, 3);           //54  : [V]
      $this->T128[] = array(3, 1, 1, 3, 2, 1);           //55  : [W]
      $this->T128[] = array(3, 3, 1, 1, 2, 1);           //56  : [X]
      $this->T128[] = array(3, 1, 2, 1, 1, 3);           //57  : [Y]
      $this->T128[] = array(3, 1, 2, 3, 1, 1);           //58  : [Z]
      $this->T128[] = array(3, 3, 2, 1, 1, 1);           //59  : [[]
      $this->T128[] = array(3, 1, 4, 1, 1, 1);           //60  : [\]
      $this->T128[] = array(2, 2, 1, 4, 1, 1);           //61  : []]
      $this->T128[] = array(4, 3, 1, 1, 1, 1);           //62  : [^]
      $this->T128[] = array(1, 1, 1, 2, 2, 4);           //63  : [_]
      $this->T128[] = array(1, 1, 1, 4, 2, 2);           //64  : [`]
      $this->T128[] = array(1, 2, 1, 1, 2, 4);           //65  : [a]
      $this->T128[] = array(1, 2, 1, 4, 2, 1);           //66  : [b]
      $this->T128[] = array(1, 4, 1, 1, 2, 2);           //67  : [c]
      $this->T128[] = array(1, 4, 1, 2, 2, 1);           //68  : [d]
      $this->T128[] = array(1, 1, 2, 2, 1, 4);           //69  : [e]
      $this->T128[] = array(1, 1, 2, 4, 1, 2);           //70  : [f]
      $this->T128[] = array(1, 2, 2, 1, 1, 4);           //71  : [g]
      $this->T128[] = array(1, 2, 2, 4, 1, 1);           //72  : [h]
      $this->T128[] = array(1, 4, 2, 1, 1, 2);           //73  : [i]
      $this->T128[] = array(1, 4, 2, 2, 1, 1);           //74  : [j]
      $this->T128[] = array(2, 4, 1, 2, 1, 1);           //75  : [k]
      $this->T128[] = array(2, 2, 1, 1, 1, 4);           //76  : [l]
      $this->T128[] = array(4, 1, 3, 1, 1, 1);           //77  : [m]
      $this->T128[] = array(2, 4, 1, 1, 1, 2);           //78  : [n]
      $this->T128[] = array(1, 3, 4, 1, 1, 1);           //79  : [o]
      $this->T128[] = array(1, 1, 1, 2, 4, 2);           //80  : [p]
      $this->T128[] = array(1, 2, 1, 1, 4, 2);           //81  : [q]
      $this->T128[] = array(1, 2, 1, 2, 4, 1);           //82  : [r]
      $this->T128[] = array(1, 1, 4, 2, 1, 2);           //83  : [s]
      $this->T128[] = array(1, 2, 4, 1, 1, 2);           //84  : [t]
      $this->T128[] = array(1, 2, 4, 2, 1, 1);           //85  : [u]
      $this->T128[] = array(4, 1, 1, 2, 1, 2);           //86  : [v]
      $this->T128[] = array(4, 2, 1, 1, 1, 2);           //87  : [w]
      $this->T128[] = array(4, 2, 1, 2, 1, 1);           //88  : [x]
      $this->T128[] = array(2, 1, 2, 1, 4, 1);           //89  : [y]
      $this->T128[] = array(2, 1, 4, 1, 2, 1);           //90  : [z]
      $this->T128[] = array(4, 1, 2, 1, 2, 1);           //91  : [{]
      $this->T128[] = array(1, 1, 1, 1, 4, 3);           //92  : [|]
      $this->T128[] = array(1, 1, 1, 3, 4, 1);           //93  : [}]
      $this->T128[] = array(1, 3, 1, 1, 4, 1);           //94  : [~]
      $this->T128[] = array(1, 1, 4, 1, 1, 3);           //95  : [DEL]
      $this->T128[] = array(1, 1, 4, 3, 1, 1);           //96  : [FNC3]
      $this->T128[] = array(4, 1, 1, 1, 1, 3);           //97  : [FNC2]
      $this->T128[] = array(4, 1, 1, 3, 1, 1);           //98  : [SHIFT]
      $this->T128[] = array(1, 1, 3, 1, 4, 1);           //99  : [Cswap]
      $this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]
      $this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
      $this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
      $this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
      $this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
      $this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
      $this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
      $this->T128[] = array(2, 1);                       //107 : [END BAR]

      for ($i = 32; $i <= 95; $i++)
        $this->ABCset .= chr($i);

      $this->Aset = $this->ABCset;
      $this->Bset = $this->ABCset;
      $this->Cset = "0123456789";

      for ($i = 0; $i <= 31; $i++)
      {
        $this->ABCset .= chr($i);
        $this->Aset   .= chr($i);
      }

      for ($i = 96; $i <= 126; $i++)
      {
        $this->ABCset .= chr($i);
        $this->Bset   .= chr($i);
      }

      for ($i = 0; $i < 96; $i++)
      {
        @$this->SetFrom["A"] .= chr($i);
        @$this->SetFrom["B"] .= chr($i + 32);
        @$this->SetTo["A"]   .= chr(($i < 32) ? $i + 64 : $i - 32);
        @$this->SetTo["B"]   .= chr($i);
      }
    }

    function Code128($x, $y, $code, $w, $h)
    {
      $this->PDF_Code128();

      $Aguid = "";
      $Bguid = "";
      $Cguid = "";

      for ($i = 0; $i < strlen($code); $i++)
      {
        $needle = substr($code, $i, 1);
        $Aguid .= ((strpos($this->Aset, $needle) === false) ? "N" : "O");
        $Bguid .= ((strpos($this->Bset, $needle) === false) ? "N" : "O");
        $Cguid .= ((strpos($this->Cset, $needle) === false) ? "N" : "O");
      }

      $SminiC = "OOOO";
      $IminiC = 4;

      $crypt = "";

      while ($code > "")
      {
        $i = strpos($Cguid, $SminiC);

        if ($i !== false)
        {
          $Aguid[$i] = "N";
          $Bguid[$i] = "N";
        }

        if (substr($Cguid, 0, $IminiC) == $SminiC)
        {
          $crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);
          $made   = strpos($Cguid, "N");

          if ($made === false)
            $made = strlen($Cguid);

          if (fmod($made, 2) == 1)
            $made--;

          for ($i = 0; $i < $made; $i += 2)
            $crypt .= chr(strval(substr($code, $i, 2)));

          $set = "C";
        }
        else
        {
          $madeA = strpos($Aguid, "N");

          if ($madeA === false)
            $madeA = strlen($Aguid);

          $madeB = strpos($Bguid, "N");

          if ($madeB === false)
            $madeB = strlen($Bguid);

          $made = (($madeA < $madeB) ? $madeB : $madeA);
          $set  = (($madeA < $madeB) ? "B" : "A");

          $crypt .= chr(($crypt > "") ? $this->JSwap[$set] : $this->JStart[$set]);

          $crypt .= strtr(substr($code, 0, $made), $this->SetFrom[$set], $this->SetTo[$set]);
        }

        $code  = substr($code,  $made);
        $Aguid = substr($Aguid, $made);
        $Bguid = substr($Bguid, $made);
        $Cguid = substr($Cguid, $made);
      }

      $check = ord($crypt[0]);

      for ($i = 0; $i < strlen($crypt); $i++)
        $check += (ord($crypt[$i]) * $i);

      $check %= 103;

      $crypt .= chr($check) . chr(106) . chr(107);

      $i = (strlen($crypt) * 11) - 9;
      $modul = $w / $i;

      for ($i = 0; $i < strlen($crypt); $i++)
      {
        $c = $this->T128[ord($crypt[$i])];

        for ($j = 0; $j < count($c); $j++)
        {
          $this->Rect($x, $y, $c[$j] * $modul, $h, "F");
          $x += (($c[$j++] + $c[$j]) * $modul);
        }
      }
    }

    // #################################################
    // # http://www.fpdf.org/en/script/script62.php    #
    // #################################################

    //Cell with horizontal scaling if text is too wide
    function CellFit($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $scale=false, $force=true)
    {
      //Get string width
      $str_width=($this->GetStringWidth($txt)>0?$this->GetStringWidth($txt):1);

      //Calculate ratio to fit cell
      if($w==0)
        $w = $this->w-$this->rMargin-$this->x;
      $ratio = ($w-$this->cMargin*2)/$str_width;

      $fit = ($ratio < 1 || ($ratio > 1 && $force));
      if ($fit)
      {
        if ($scale)
        {
          //Calculate horizontal scaling
          $horiz_scale=$ratio*100.0;
          //Set horizontal scaling
          $this->_out(sprintf('BT %.2F Tz ET',$horiz_scale));
        }
        else
        {
          //Calculate character spacing in points
          $char_space=($w-$this->cMargin*2-$str_width)/max($this->MBGetStringLength($txt)-1,1)*$this->k;
          //Set character spacing
          $this->_out(sprintf('BT %.2F Tc ET',$char_space));
        }
        //Override user alignment (since text will fill up cell)
        $align='';
      }

      //Pass on to Cell method
      $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);

      //Reset character spacing/horizontal scaling
      if ($fit)
        $this->_out('BT '.($scale ? '100 Tz' : '0 Tc').' ET');
    }

    //Cell with horizontal scaling only if necessary
    function CellFitScale($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
      $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,true,false);
    }

    //Cell with horizontal scaling always
    function CellFitScaleForce($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
      $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,true,true);
    }

    //Cell with character spacing only if necessary
    function CellFitSpace($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
      $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,false,false);
    }

    //Cell with character spacing always
    function CellFitSpaceForce($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
      //Same as calling CellFit directly
      $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,false,true);
    }

    //Patch to also work with CJK double-byte text
    function MBGetStringLength($s)
    {
      if($this->CurrentFont['type']=='Type0')
      {
        $len = 0;
        $nbbytes = strlen($s);
        for ($i = 0; $i < $nbbytes; $i++)
        {
          if (ord($s[$i])<128)
            $len++;
          else
          {
            $len++;
            $i++;
          }
        }
        return $len;
      }
      else
        return strlen($s);
    }
  }

  //Handle special IE contype request
  if(isset($_SERVER['HTTP_USER_AGENT']) and $_SERVER['HTTP_USER_AGENT']=='contype')
  {
    Header('Content-Type: application/pdf');
    exit;
  }
