<?php

if (false === function_exists('get_artomator_template_file_path')) {
    /**
     * get path for template file.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    function get_artomator_template_file_path($templateName, $templateType)
    {
        $templateName = str_replace('.', '/', $templateName);

        $templatesPath = config(
            'infyom.laravel_generator.path.templates_dir',
            resource_path('infyom/infyom-generator-templates/')
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

if (false === function_exists('get_artomator_template')) {
    /**
     * get template contents.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    function get_artomator_template($templateName, $templateType = 'artomator')
    {
        $path = get_artomator_template_file_path($templateName, $templateType);

        return file_get_contents($path);
    }
}//end if

if (false === function_exists('license_authors')) {
    /**
     * format authors for codeblock.
     *
     * @param string|array $authors
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
}// end if
