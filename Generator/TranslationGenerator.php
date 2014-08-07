<?php

namespace ServerGrove\Bundle\TranslationEditorBundle\Generator;

use ServerGrove\Bundle\TranslationEditorBundle\Document\Locale;
use ServerGrove\Bundle\TranslationEditorBundle\Storage\StorageInterface;

use Avro\TranslatorBundle\Translator\TranslatorInterface;

class TranslationGenerator
{

    protected $storageService;
    protected $translator;

    /**
     * @param StorageInterface $storageService
     * @param TranslaterIterface $translator
     */
    public function __construct(StorageInterface $storageService, TranslatorInterface $translator)
    {
        $this->storageService = $storageService;
        $this->translator = $translator;
    }

    /**
     * Converts translations from one language to another
     *
     * @param string $from The language to translate from
     * @param string $to The language to translate to
     */
    public function run($from, $to, $progress = false)
    {
        $localeList = $this->storageService->findLocaleList(array('language' => $from));

        if (count($localeList) == 0) {
            throw new \Exception(sprintf('Locale "%s" does not exist', $from));
        }

        $locale = reset($localeList);

        $toLocaleList = $this->storageService->findLocaleList(array('language' => $to));

        $toLocale = reset($toLocaleList);

        if (!$toLocale instanceOf Locale) {
            $toLocale = $this->storageService->createLocale($to);

            $this->storageService->flush();
        }

        $translations = $this->storageService->findTranslationList(array('locale' => $locale));

        foreach ($translations as $translation) {
            $newValue = $this->translator->translate($translation->getValue(), $from, $to);

            $this->storageService->createTranslation($toLocale, $translation->getEntry(), $newValue);

            $this->storageService->flush();

            if ($progress) {
                echo '.';
            }
        }
    }
}
