# FASTER

_This is FASTER, a new silky-smooth 2.5D racing game built from the ground up for the Atari STE using modern tools, techniques and knowledge._

![Screenshot of current progress](https://github.com/jonathanopalise/le-mans-minigame/blob/main/screenshot.png)

## Credits

* **Design and programming**: Chicane (Jonathan Thomas)
* **Music**: Dma-sc
* **SFX/Mixer enhancement**: Junosix (Jamie Hamshere)
* **Additional art**: Karl Morris, Scott Elkington
* **YM playback**: Zerkman
* **Original PCM mixer**: Masteries 

## About this source code

* The code is a solid example of how to create a high-performance STE game using modern tools, techniques and knowledge;
* The code is not clean, maintainable, for beginners, or in any way representative of best practice :)

## Directory layout

Directory layout is as follows:

* `bin` - contains .PRG files generated by means of compiling the code in `src`;
* `release` - contains a bootable disk image named `lemans.st` upon successful completion of the build process;
* `src` - contains the core source code to generate the code for use on the ST side. There is a `generated` subdirectory within that contains machine generated source files;
* `libcxx` - contains compiler-specific pre-built object files for core libraries and executable bootstrapping;
* `assets` - contains graphics files used by the build process.
* `diskcontent` - contains files ready to be written to the game's disk image that require no compilation or preprocessing.

## How to build

The build process is controlled by a `Makefile`. The `Makefile` is confirmed to work with Mac OSX, but I would expect it to work also with Linux and possibly other Unix variants. It could possibly be repurposed for Windows with some changes - please get in touch if you can help. Note that the source is currently only compatible with the "bigbrownbuild" GCC compiler and you will need to build this specific compiler even if you have other C compilers present on your machine. 

Run `make` to start the build process. The following executable dependencies will need to be present in the path:

- `vasmm68k_mot` (http://sun.hasenbraten.de/vasm/)
- `vlink` (http://sun.hasenbraten.de/vlink/)
- `m68k-atarimegabrowner-elf-gcc` (https://bitbucket.org/ggnkua/bigbrownbuild-git/src/master/)
- `php` (https://www.php.net/)
- `zip2st` (packaged with the Hatari emulator - https://hatari.tuxfamily.org/)
- `zip` (commonly provided by package managers)
- `upx` (https://upx.github.io/)
- `mdel` and `mcopy` (part of mtools and commonly provided by package managers)

Should the build process succeed, there will be a `lemans.st` file present within the `release` directory that can be run within an emulator such as Hatari (https://hatari.tuxfamily.org/) or transferred elsewhere to run on real STE hardware. Whether running on an emulator or real hardware, the machine will need to be configured as an STE with one meg or more of memory. In the event that the build process fails, please raise an issue against the project and I'll help in any way I can.
