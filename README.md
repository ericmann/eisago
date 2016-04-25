εισάγω
======

"eisago" means "import" in Greek.

Imperative
----------

The importer should:
- Get a list of files in the data directory
- For each file, allocate a file pointer
  - Read a line in and create a Verse
  - Store the Verse to the database

Once complete, the console will display "Done" and a randomly-selected verse from Proverbs.

Concurrent
----------

The importer should:
- Get a list of files in the data directory
- For each file, allocate a Reader object that returns a Promise
  - The Promise will allocate a file pointer
    - Read a line in and create a Verse
    - Store the Verse to the database
  - The Promise will resolve with the number of verses read
  
Once the Promise for each book resolves, the Verses column will be updated with the total verse count. Once all books are
complete, the console will display "Done" and a randomly-selected verse from Matthew.

Parallel
--------

The importer should:
- Get a list of files in the data directory
- Create up to 5 Processor threads that will each allocate a Reader object and `join` the main thread upon resolving its Promise
- Follow the Concurrent workflow for Reader resolution
- Continue utilizing threads until all imports are resolved

The console output will be identical to the Concurrent workflow. Once complete, the console will display "Done" and a
randomly-selected verse from James.