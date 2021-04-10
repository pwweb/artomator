<?php

if (false === function_exists('get_artomator_template_file_path')) {
    /**
     * Get path for template file.
     *
     * @param string $templateName Template name.
     * @param string $templateType Template type.
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

        if (true === file_exists($path)) {
            return $path;
        }

        if (true === file_exists(base_path('vendor/pwweb/'.$templateType.'/templates/'.$templateName.'.stub'))) {
            return base_path('vendor/pwweb/'.$templateType.'/templates/'.$templateName.'.stub');
        }

        return get_template_file_path($templateName, 'laravel-generator');
    }
}//end if

if (false === function_exists('get_artomator_template')) {
    /**
     * Get template contents.
     *
     * @param string $templateName Template name.
     * @param string $templateType Template type.
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
     * Format authors for codeblock.
     *
     * @param string|array $authors Authors.
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
