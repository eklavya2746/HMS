<?php
spl_autoload_register(function ($class) {
    // Define the directory where your class files are located
    $directory = __DIR__ . '/classes/';
    
    // Replace the namespace separator with the directory separator
    $classFile = $directory . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    
    // Include the class file if it exists
    if (file_exists($classFile)) {
        require $classFile;
    }
});
