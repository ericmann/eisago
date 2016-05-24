εισάγω
======

"eisago" means "import" in Greek.

Basic Helper Functionality
--------------------------

### `BaseImporter`

Use `::getFileList()` to get a list of files in the directory (since we need to iterate over each)

Use `::importLine()` to convert a string into a `Verse` object and enter it into the MongoDB instance

### `Counter`

Use `::countFrom()` to retrieve a total linecount for a given file

### `Reader`

Use `::readInto()` to read a file line-by-line, passing each line into a specified callback method

### `OutputWriter`

The `->output` instance variable in each importer class is an `OutputWriter` instance with the following supporting methods:

- Use `::addBook()` to add a book to the table for display
- Use `::printTable()` to render the contents of the table to the CLI

Workflows
---------

### Imperative

The importer should:
- Get a list of files in the data directory
- For each file, allocate a file pointer
  - Read a line in and create a Verse
  - Store the Verse to the database

Once complete, the console will display "Done" and a randomly-selected verse from Proverbs.

### Concurrent

The importer should:
- Get a list of files in the data directory
- For each file, build a Promise that will:
  - Read a line in and create a Verse
  - Store the Verse to the database
  - The Promise will resolve with the name of the book imported
  
Once the Promise for each book resolves, the Verses column will be updated with the total verse count. Once all books are
complete, the console will display "Done" and a randomly-selected verse from Matthew.

### Parallel

The importer should:
- Get a list of files in the data directory
- Create individual processor threads that will each allocate a reader object and `join` the main thread upon resolving its Promise
- Follow the Concurrent workflow for Promise resolution
- Continue utilizing threads until all imports are resolved

The console output will be identical to the Concurrent workflow. Once complete, the console will display "Done" and a
randomly-selected verse from James.