<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\Loader\ArrayLoader;

class UpdateMessagesCommand extends ContainerAwareCommand{
    /**
     * Configure Command
     */
    protected function configure()
    {
        $this
            // the name of the command (the part after "app/console")
            ->setName('skygate:translations:update')
            // the short description shown while running "php app/console list"
            ->setDescription('Checks for message key with missing translations and saves a key for them')
            //to update all the entries with empty coordinates_saved_at columns
            ->addOption('prefix',null,InputOption::VALUE_OPTIONAL, 'The name of the file, which should be checked')
            //to skip the entries of AdditionalQualification and update only the entries of DualStudy with empty coordinates_saved_at columns
            ->addOption('langs',null,InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The languages for the translation')
            //to skip the entries of DualStudy and update only the entries of AdditionalQualification with empty coordinates_saved_at columns
            ->addOption('path',null, InputOption::VALUE_OPTIONAL, 'The path to the file')
            // the "--help" option    
            ->setHelp('This command allows you to update the translations key in a messages.yml file')
    ; }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $prefix = $input->getOption('prefix');
        $langs = $input->getOption('langs');
        $path = $input->getOption('path');
        
        if(!$prefix){
            $prefix = "messages";
            $output->writeln('Prefix: ' .$prefix);
        } else {
            $prefix = $input->getOption('prefix');
            $output->writeln('Prefix: ' .$prefix);
        }
        
        if(empty($langs)){
            $langs = array();
            array_push($langs, "de", "en");
            foreach ($langs as $lang){
                $output->writeln('Langs: ' .$lang);
            }
        } else {
            $langs = $input->getOption('langs');
            foreach ($langs as $lang){
                $output->writeln('Langs: ' .$lang);
            }
        }
        var_dump($langs);
        if(!$path){
            $path = "app/Resources/translations/";
            $output->writeln('Path: '.$path);
        } else {
            $path = $input->getOption('path');
            $output->writeln('Path: '.$path);
        }
        
        function show_files($path, $lang, $ftype)
        {
            $dir = opendir ($path);
            while (false !== ($file = readdir($dir)))
            {
               if (strpos($file, $ftype,1))
               {
                 $filelist[] = $file;
               }
            }
            return $filelist;
         }
 
        foreach($langs as $lang){
            $ergebnis = show_files($path,$lang, ".yml");
//            var_dump($ergebnis);
//            die;
//            $ergebnisEn = show_files($path, "en.yml");
        }
 
 
        //$output->writeln($ergebnis);

        $output->writeln('');
        foreach ($ergebnis as $erg){
            $value1 = Yaml::parse(file_get_contents("app/Resources/translations/" .$erg));
            $loader = new ArrayLoader();
            $flattenedArrayDe = $loader->load($value1, "de");
            echo "<pre>";
            print_r($flattenedArrayDe->all());
            echo "</pre>";
            echo " ";
        }
        
//        $output->writeln('ENGLISCH');
//        foreach ($ergebnisEn as $erg){
//            $value2 = Yaml::parse(file_get_contents("app/Resources/translations/" .$erg));
//            $loader = new ArrayLoader();
//            $flattenedArrayEn = $loader->load($value2, "en");
//            echo "<pre>";
//            print_r($flattenedArrayEn->all());
//            echo "</pre>";
//            echo " ";
//        }
        
//        foreach ($ergebnisEn as $erg){
//            $diff = array_diff_key($value2, $value1);
//            print_r($diff);
//            unset($diff);
//        }
//        echo " ";
//        $flattenedArrayEn = $loader->load($value, "en");
//        echo "<pre>";
//        print_r($flattenedArrayEn->all());
//        echo "</pre>";
////            print_r($value);
//            echo Yaml::dump($value, 1);
//            foreach($value as $key => $val){
//                $output->writeln($value);
//                $output->writeln("$key => $val");
////                if(isset($value[0])){
////                $result = array_diff_key($value[1], $value[0]);}
////                if(isset($result)){
////                    $output->writeln($result);
////                }else{ $output->writeln("Keine Unterschiede!");}
//                }
////                $result = array_diff($value, $val);
////                $output->writeln($result);
//            } 
//            $output->writeln('');
//        }       
//        } 
    }
}      

// $translations = Yaml::parse(file_get_contents('F:\Workspaces\KPMG\kpmg_keasy_be\app\Resources\translations\messages.de.yml'));
//        $loader = new ArrayLoader();
//        $flattenedArray = $loader->load($translations, "de")->all();
//        echo "<pre>";
//        print_r($flattenedArray->all());
//        echo "</pre>";