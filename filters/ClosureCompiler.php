<?php
namespace WebLoader\Filters;


/**
 * Filter using Google Closure Compiler API
 * @see http://code.google.com/intl/cs/closure/compiler/docs/api-tutorial1.html
 * @author Jan Prachař <jan.prachar@intya.cz>
 */
class ClosureCompiler
{
    public $compilationLevel = 'SIMPLE_OPTIMIZATIONS';


    public function __invoke($code)
    {
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, 'http://closure-compiler.appspot.com/compile');
        \curl_setopt($ch, \CURLOPT_POST, 1);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, \http_build_query(array(
            'js_code' => $code,
            'compilation_level' => $this->compilationLevel,
            'output_format' => 'text',
            'output_info' => 'compiled_code'
        )));

        $result = \curl_exec($ch);
        \curl_close($ch);

        if ($result === FALSE || $result === "\x0a") {//connection or syntax error
            return $code;
        }
        return $result;
    }
}