<?php

if (! function_exists('get_template_file_path')) {
    /**
     * get path for template file.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    function get_template_file_path($templateName, $templateType)
    {
        $templateName = str_replace('.', '/', $templateName);

        $templatesPath = config(
            'pwweb.artomator.path.templates_dir',
            resource_path('pwweb/artomator-templates/')
        );

        $path = $templatesPath.$templateName.'.stub';

        if (file_exists($path)) {
            return $path;
        }

        if (file_exists(base_path('vendor/pwweb/'.$templateType.'/templates/'.$templateName.'.stub'))) {
            return base_path('vendor/pwweb/'.$templateType.'/templates/'.$templateName.'.stub');
        }

        return base_path('vendor/infyomlabs/'.$templateType.'/templates/'.$templateName.'.stub');
    }
}//end if

if (! function_exists('license_authors')) {
    /**
     * format authors for codeblock.
     *
     * @param string|[string] $authors
     *
     * @return string
     */
    function license_authors($authors)
    {
        if (true === is_array($authors)) {
            return implode("\n * @author    ", $authors);
        } else {
            return $authors;
        }
    }
}
