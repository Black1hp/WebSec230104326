<<<<<<< HEAD
<?php

=======

<?php
   
>>>>>>> Midterm-v2
if (!function_exists('isPrime')) {
    function isPrime($number)
    {
        if($number<=1) return false;
        $i = $number - 1;
        while($i>1) {
<<<<<<< HEAD
            if($number%$i==0) return false;
            $i--;
=======
        if($number%$i==0) return false;
        $i--;
>>>>>>> Midterm-v2
        }
        return true;
    }
}
