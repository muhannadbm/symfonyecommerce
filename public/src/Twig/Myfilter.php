<?php

namespace App\Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
class Myfilter extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('timesince', [$this, 'Myfilter'])
        ];
    }
     
     public function Myfilter($datetime){

        $time = time() - strtotime($datetime); 

        $units = array (
          31536000 => 'سنة',
          2592000 => 'شهر',
          604800 => 'اسبوع',
          86400 => 'يوم',
          3600 => 'ساعة',
          60 => 'دقيقة',
          1 => 'ثانية'
        );
      
        foreach ($units as $unit => $val) {
          if ($time < $unit) continue;
          $numberOfUnits = floor($time / $unit);
          return ($val == 'ثانية')? 'منذ عدة ثواني' : 
                 (($numberOfUnits>1) ? 'منذ' : 'منذ').' '.$numberOfUnits.' '.$val;
        }
     }


}