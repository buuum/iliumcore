<?php

namespace Ilium\Dependency;

use League\Container\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCommand extends Command
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->output = $output;

        $this->fire();
    }

    abstract protected function fire();

    /**
     * @param $question
     * @param null $default
     * @return mixed
     */
    protected function question($question, $default = null)
    {
        $question = new Question($question, $default);

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }


    protected function choiceQuestion($question, $answers, $default = null, $multiple = false)
    {
        $question = new ChoiceQuestion(
            $question,
            $answers,
            $default
        );

        if ($multiple) {
            $question->setMultiselect(true);
        }

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }

    /**
     * @param $question
     * @return mixed
     */
    protected function confirm($question)
    {
        $question = new ConfirmationQuestion($question, false);

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }

    /**
     * @param $message
     */
    protected function success($message)
    {
        $this->output->writeln("<info>$message</info>");
    }

    /**
     * @param $message
     */
    protected function error($message)
    {
        $this->output->writeln("<error>$message</error>");
    }

    /**
     * @param $message
     */
    protected function comment($message)
    {
        $this->output->writeln("$message");
    }

    /**
     * @param $message
     */
    protected function debug($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln($message);
        }
    }
}