<?php

namespace ServerGrove\Bundle\TranslationEditorBundle\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Translation\MessageCatalogue;


/**
 * Command for generating new translation files
 *
 * @author Joris de Wit <joris.w.dewit@gmail.com>
 */
class GenerateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('locale:editor:generate')
            ->setDescription('Creates a translation file for a locale automatically')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $dialog = $this->getHelper('dialog');

        $storage = $this->getContainer()->get('server_grove_translation_editor.storage');

        $localesArray = array();
        $locales = $storage->findLocaleList();

        foreach($locales as $locale) {
            $localesArray[] = $locale->getLanguage();
        }

        $fromIndex = $dialog->select(
            $output,
            'Please select the locale to translate from',
            $localesArray,
            0
        );

        $from = $localesArray[$fromIndex];

        $to = $dialog->ask(
            $output,
            'Please enter the locale of the language to translate to: ',
            ''
        );

        if (strlen($to) != 2) {
            throw new \Exception('Locale must be 2 digits');
        }

        $generator = $this->getContainer()->get('server_grove_translation_editor.translation.generator');

        $this->output->writeln('Please wait while your translations are translated.');

        $generator->run($from, $to, true);

        $this->output->writeln('Done.');
    }

}
