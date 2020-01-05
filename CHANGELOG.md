# Change Log

## [Unreleased]
### Added
- Add PluralizeTrait.
- Add VerbosityTrait.

### Changed
- Refactor HandlesFunctionTrait to AbstractFunctionsBasedBenchmark.
- Update Informer with Xdebug information.
- Update .travis.yml 7.4 alias.

## [2.0.0] - 2020-01-03
### Added
- Add Input interface and CLI realization.
- Add Output interface and CLI realization.
- Add ArgumentsHandler interface and CLI realization.
- Add new CLI entry point.
- Add own exceptions.

### Changed
- Totally reorganized application structure.
- Extract Informer from Benchmarks.
- Extract Benchmarks from Benchmarks.
- Move Benchmarks to Application class.
- Move Reporters to Presenters.
- Reorganize and update tests.
- Update README.md.

## [1.1.0] - 2019-11-23
### Added
- Add exclude benchmark list option.
- Add Filesystem returns read/write time.
- Add formatExecutionTimeBatch (formats all output time).

### Changed
- Rearrange benchmarks internal representation format.
- Update benchmarks initialization and processing.
- Rearrange all tests to use runPrivateMethod helper.

## [1.0.0] - 2019-11-20
### Added
- Add main a executable file (composer bin property)
- Add all option to execute all available benchmarks.
- Add time shortening in default mode.
- Add Filesystem prefix, precision, rounding options.

### Changed
- Increase calculation time accuracy.
- Update Filesystem data precision mechanism. 

## [1.0.0-RC] - 2019-10-28
### Added
- Add Filesystem benchmark.
- Add passing options to benchmarks.
- Add iterations number option.
- Add verbose mode.

### Changed
- Refactor similar benchmarks to HandleFunctionTrait trait.
- Reorganize additional testing functions in benchmarks.
- Update CliReporter with output styling.
- Reorganize benchmarks naming.

## [1.0.0-beta] - 2019-10-25
### Added
- Add Benchmark class.
- Add a basic set of benchmarks.
- Add Reporter class.
- Project information.
- Add debug mode.
- Add CHANGELOG.md
- Add README.md
- Add .travis.yml