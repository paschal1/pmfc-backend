<?php


use Illuminate\Support\Str;

/**
 * @param int $len
 * @param array $param
 *  return string - token;
 */
if( ! function_exists('generate_token') ){
    function generate_token(int $len = 8, array $param = [], mixed $type='mixed') {

        if($param) {
            $token = generate_until_unique($param, function() use ($len, $type) {
                return ($type === 'number')
                    ? generate_numbers($len)
                    : generate_character($len, true);
            });
        } else {
            $token = ($type === 'number')
                ? generate_numbers($len)
                : generate_character($len, true);
        }

        return $token;
    }
}

if(! function_exists('generate_character')) {
    function generate_character($len, bool $removeConfusingCharacters=false): string
    {
        $char = Str::random($len);
        if($removeConfusingCharacters) {
            $confusing_characters = [0, 1, 'O', 'i', 'o', 'I'];
            $char = str_replace($confusing_characters, 'a' ,$char);
        }
        return $char;

    }
}


if (!function_exists('generate_numbers')) {
    function generate_numbers($len, $removeZero = false)
    {
        $min = 1 . str_repeat(0, $len-1);
        $max = (int)str_repeat(9, $len);
        $numbers = mt_rand($min, $max);

        if ($removeZero) {
            $numbers = str_replace(0, 1, $numbers);
        }

        return $numbers;
    }
}

if(! function_exists('generate_until_unique')) {
    /**
     * @param array | object $param
     * @param $callback
     * @param string $column
     * @return mixed
     */



    function generate_until_unique(array | object $param,  $callback, string $column='id'): mixed {
        $field = 'token';
        $model = $param;
        if(is_array($param)) {

            if(isset($param['field'])) {
                $field = $param['field'];
                $model = $param['model'];
            } else {
                if(is_string($param[0])) {
                    $field = $param[0];
                    $model = $param[1];
                } else {
                    $field = $param[1];
                    $model = $param[0];
                }

            }
        }

        do{
            $char = $callback();
        }while($model->where($field, $char)->first([$column]));

        return $char;
    }
}
