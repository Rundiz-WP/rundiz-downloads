<?php
/**
 * Simple template parser
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Template')) {
    /**
     * Simple template parser class.
     * 
     * You can use these template code:
     * <pre>
     * {{variable_name}} For convert from variable to its value.
     * {if condition_name}
     *     The `condition_name` is same as variable name, if it exists then this condition is true.
     *     You can write it out ({{condition_name}}).
     * {endif}
     * {#
     *     The comment block.
     *     Everything in here (where comment block must be in root) will be skipped.
     *     Example: {{variable_name}} <- This will be skipped and not parsing.
     * #}
     * </pre>
     */
    class Template
    {


        /**
     * @var string The PCRE pattern for allowed template variable name.
     */
        protected $allowedTemplateVariable = 'a-z0-9\_';


        /**
         * @var array Template data where array key matched in template variable.
         */
        protected $templateData = [];


        /**
         * @var string The original template string.
         */
        protected $templateString;


        /**
         * Assign template variable name and its value.
         *
         * @param string $tVarName Template variable name must be alpha-numeric, underscore, NOT use words: if, endif.
         * @param mixed $value Template value can be anything but should be scalar type.
         */
        public function assign($tVarName, $value)
        {
            if (
                is_scalar($tVarName) && 
                preg_match('#^[' . $this->allowedTemplateVariable . ']+$#iu', $tVarName) && 
                (strtolower($tVarName) != 'if' && strtolower($tVarName) != 'endif')
            ) {
                $this->templateData[$tVarName] = $value;
            }
        }// assign


        /**
         * Convert template to HTML result and return.
         *
         * @link https://stackoverflow.com/questions/24987518/php-preg-match-all-search-and-replace Reference.
         * @link https://stackoverflow.com/a/28392186/128761 Reference for recursive pattern.
         * @link https://github.com/twigphp/Twig/blob/2.x/lib/Twig/Lexer.php Reference for token start pattern idea.
         * @throws \Exception Throw error if no template content was set.
         * @return string Return processed template.
         */
        public function get()
        {
            if (empty($this->templateString)) {
                throw new \Exception('Please call setTemplate() method to set the template content first.');
            }

            $template = $this->templateString;
            $replaces = $this->templateData;

            $pattern = '(';// open template tags. $m[1] is {{, {if xxx}, {#.
            $pattern .= '\{\{[\s]*';// {{
            $pattern .= '|\{[\s]*if[\s]*(.+?)[\s]*\}';// {if (.+?)}. $m[2] is condition inside if.
            $pattern .= '|\{#';// {#
            $pattern .= ')';

            $pattern .= '((?>(?R)|.)*?)';// content. $m[3] is content. {{xxx}} content is xxx, {if xxx}yyy{endif} content is yyy, {#aaa#} content is aaa.

            $pattern .= '(';// close template tags. $m[4] is }}, {endif}, #}
            $pattern .= '[\s]*\}\}';// }}
            $pattern .= '|\{[\s]*endif[\s]*\}';// {endif}
            $pattern .= '|#\}';// #}
            $pattern .= ')';

            $template = preg_replace_callback('/' . $pattern . '/siu', function($m) use($replaces) {
                if (is_array($m) && count($m) >= 5) {
                    if (trim($m[1]) == '{{' && trim($m[4]) == '}}' && isset($replaces[trim($m[3])])) {
                        return $replaces[trim($m[3])];
                    }
                    if (preg_match('/\{[\s]*if[\s]*(.+?)[\s]*\}/iu', trim($m[1])) && preg_match('/\{[\s]*endif[\s]*\}/iu', trim($m[4]))) {
                        // if it is contain `{if xxx}xxx{endif}` condition.
                        if (isset($m[2]) && isset($replaces[trim($m[2])]) && !empty($replaces[trim($m[2])])) {
                            // if condition inside this `if` passed.
                            return $this->replaceVariable($m[3], $replaces);
                        } else {
                            return '';
                        }
                    }
                    if (trim($m[1]) == '{#' && trim($m[4]) == '#}') {
                        return $m[0];
                    }
                }
                return '';
            }, $template);

            unset($pattern, $replaces);

            return $template;
        }// get


        /**
         * Get template data.
         *
         * @param string $name Template variable name to get. If this is empty then it will be return all.
         * @return mixed Return template data value depend on its name or return all if name is empty. Return null if not exists.
         */
        public function getTemplateData($name = '')
        {
            if (empty(trim($name))) {
                return $this->templateData;
            }

            if (is_array($this->templateData) && array_key_exists($name, $this->templateData)) {
                return $this->templateData[$name];
            }

            return null;
        }// getTemplateData


        /**
         * Replace variable and return value.
         *
         * @param string $template The template string.
         * @param array $replaces The replaces data in associate array.
         * @return string Return replaced variable name to its value.
         */
        protected function replaceVariable($template, $replaces)
        {
            $pattern = '\{\{[\s]*';
            $pattern .= '((?>(?R)|.)*?)';
            $pattern .= '[\s]*\}\}';

            $template = preg_replace_callback('/' . $pattern . '/siu', function($m) use($replaces) {
                if (isset($replaces[trim($m[1])])) {
                    return $replaces[trim($m[1])];
                }

            }, $template);

            return $template;
        }// replaceVariable


        /**
         * Set template string
         *
         * @param string $templateString The template string.
         * @param array $data Template data to be assign.
         */
        public function setTemplate($templateString, array $data = [])
        {
            $this->templateString = $templateString;
            $templateString = null;

            foreach ($data as $tName => $value) {
                $this->assign($tName, $value);
            }// endforeach;
            unset($tName, $value);
        }// setTemplate


    }
}