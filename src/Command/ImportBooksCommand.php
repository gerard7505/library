<?php

namespace App\Command;

use App\Service\BookImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-books', 
    description: 'Importa libros desde un archivo JSON a la base de datos.'
)]
class ImportBooksCommand extends Command
{
    private BookImporter $bookImporter;

    public function __construct(BookImporter $bookImporter)
    {
        parent::__construct();
        $this->bookImporter = $bookImporter;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Importa libros desde un archivo JSON a la base de datos.')
            ->addArgument('filePath', InputArgument::REQUIRED, 'Ruta del archivo JSON con los libros');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Obtener la ruta del JSON desde el argumento
        $filePath = $input->getArgument('filePath');

        // Validar si el archivo existe
        if (!file_exists($filePath)) {
            $io->error("El archivo no existe en la ruta: $filePath");
            return Command::FAILURE;
        }

        // Validar que el archivo tenga extensión .json
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'json') {
            $io->error("El archivo proporcionado no es un JSON válido.");
            return Command::FAILURE;
        }

        try {
            $this->bookImporter->importFromJson($filePath);
            $io->success('Libros importados correctamente.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error al importar libros: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
