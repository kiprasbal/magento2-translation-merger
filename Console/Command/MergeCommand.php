<?php
namespace STM\TranslationMerger\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeCommand extends Command
{

    /**
     * Default locale code
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * Default input csv directory
     */
    const DEFAULT_INPUT_DIR = 'app/i18n/';

    /**
     * Default output csv directory
     */
    const DEFAULT_OUTPUT_DIR = 'app/design/frontend/base/i18n/';

    /**
     * Input directory argument name
     */
    const INPUT_DIR_ARGUMENT = 'input-directory';

    /**
     * Output directory argument name
     */
    const OUTPUT_DIR_ARGUMENT = 'output-directory';

    /**
     * Locale argument name
     */
    const LOCALE_ARGUMENT = 'locale';

    protected function configure()
    {
        $this->setName('translation-merger:merge')
            ->setDescription(
                'Merge translations from magento i18n:collect command result with current theme translations')
        ->setDefinition([
        new InputArgument(
            self::INPUT_DIR_ARGUMENT,
            InputArgument::OPTIONAL,
            'Input directory of collected Magento CSV file. (Default: '. self::DEFAULT_INPUT_DIR .')',
            self::DEFAULT_INPUT_DIR
        ),
        new InputArgument(
            self::OUTPUT_DIR_ARGUMENT,
            InputArgument::OPTIONAL,
            'Output directory of collected Magento CSV file. (Default: '. self::DEFAULT_OUTPUT_DIR .')',
            self::DEFAULT_OUTPUT_DIR
        ),
        new InputArgument(
            self::LOCALE_ARGUMENT,
            InputArgument::OPTIONAL,
            'Locale (Default: '. self::DEFAULT_LOCALE .')',
            self::DEFAULT_LOCALE
        ),
    ]);
    }

    /**
     * @param $inputFile
     * @param bool $returnFirstColumn - whether to return the full row or just first column
     * @return array
     */
    private function csvToArray($inputFile, $returnFirstColumn = false)
    {
        $arr = [];
        while($row=fgets($inputFile)){
            if($returnFirstColumn) {
                $ex = explode(",", $row);
                if (count($ex) > 0)
                    $arr[] = $ex[0];
            } else {
                $arr[] = $row;
            }
        }
        return $arr;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input_dir  = $input->getArgument(self::INPUT_DIR_ARGUMENT);
        $output_dir = $input->getArgument(self::OUTPUT_DIR_ARGUMENT);
        $locale     = $input->getArgument(self::LOCALE_ARGUMENT);

        $full_input_file_path = $input_dir.$locale.'.csv';
        $full_output_file_path = $output_dir.$locale.'.csv';

        if (!file_exists($full_input_file_path)) {
            $output->writeLn('<error>Could not find input file, check your path</error>');
            exit();
        }
        if (!file_exists($full_output_file_path)) {
            $output->writeLn('<error>Could not find output file, check your path</error>');
            exit();
        }


        $iarr = $this->csvToArray(fopen($full_input_file_path,'r'));
        $oarr = $this->csvToArray(fopen($full_output_file_path,'r'), true);

        $translationsCount = 0;
        foreach ($iarr as $key => $value) {
            $firstItem = explode(",",$value)[0];
            if (!in_array($firstItem, $oarr)){
                file_put_contents($full_output_file_path, $value, FILE_APPEND);
                $translationsCount++;
                $output->write('.');
            }
        }

        $output->writeLn('<info>Done. New translations added: '.$translationsCount.'</info>');
    }

}