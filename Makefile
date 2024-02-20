CC = m68k-atarimegabrowner-elf-gcc
CFLAGS = -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -fleading-underscore -g -O3 -flto -fomit-frame-pointer -m68000 -Wl,--traditional-format -Wall
VASM = vasmm68k_mot
VASM_OPTS = -no-opt
VLINK = vlink
PHP = php

OBJECT_FILES =\
	src/lemans.o\
	src/game_loop.o\
	src/hardware_playfield.o\
	src/vbl_handler.o\
    src/road_render.o\
	src/generated/road_geometry.o\
	src/generated/road_graphics.o\
	src/initialise.o

bin/lemans.prg: $(OBJECT_FILES)
	$(CC) -o src/lemans.elf libcxx/brownboot.o libcxx/browncrti.o libcxx/browncrtn.o libcxx/browncrt++.o libcxx/zerolibc.o libcxx/zerocrtfini.o $(OBJECT_FILES) -g -O3 -flto -Wl,--emit-relocs -Wl,-e_start -Ttext=0 -nostartfiles -m68000 -fomit-frame-pointer -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -fstrict-aliasing -fcaller-saves -ffunction-sections -fdata-sections -fleading-underscore
	brownout -s -i src/lemans.elf -o bin/lemans.prg
	chmod +x bin/lemans.prg

src/lemans.o: src/lemans.c $(OBJECT_FILES)
	$(CC) $(CFLAGS) -c src/lemans.c -o src/lemans.o

src/game_loop.o: src/game_loop.c src/game_loop.h src/hardware_playfield.h src/initialise.h src/vbl_handler.h src/road_render.h
	$(CC) $(CFLAGS) -c src/game_loop.c -o src/game_loop.o

src/hardware_playfield.o: src/hardware_playfield.c src/hardware_playfield.h src/initialise.h src/vbl_handler.h src/road_render.h
	$(CC) $(CFLAGS) -c src/hardware_playfield.c -o src/hardware_playfield.o

src/vbl_handler.o: src/vbl_handler.c src/vbl_handler.h
	$(CC) $(CFLAGS) -c src/vbl_handler.c -o src/vbl_handler.o

src/road_render.o: src/road_render.c src/road_render.h src/road_graphics.h src/hardware_playfield.h
	$(CC) $(CFLAGS) -c src/road_render.c -o src/road_render.o

src/generated/road_geometry.o: src/generated/road_geometry.c src/road_geometry.h
	$(CC) $(CFLAGS) -c src/generated/road_geometry.c -o src/generated/road_geometry.o

src/generated/road_geometry.c: src/generate_road_geometry.php
	$(PHP) src/generate_road_geometry.php src/generated/road_geometry.c

src/generated/road_graphics.o: src/generated/road_graphics.c src/road_graphics.h
	$(CC) $(CFLAGS) -c src/generated/road_graphics.c -o src/generated/road_graphics.o

src/generated/road_graphics.c: src/generate_road_graphics.php
	$(PHP) src/generate_road_graphics.php src/generated/road_graphics.c

src/initialise.o: src/initialise.s
	$(VASM) $(VASM_OPTS) src/initialise.s -Felf -o src/initialise.o


