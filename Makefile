CC = m68k-atarimegabrowner-elf-gcc
CFLAGS = -nostdlib -D__ATARI__ -D__M68000__ -D__NATFEATS_DEBU -DELF_CONFIG_STACK=1024 -flto -fleading-underscore -O3 -fomit-frame-pointer -m68000 -Wall
VASM = vasmm68k_mot
VASM_OPTS =
VLINK = vlink
PHP = php
UPX = upx

OBJECT_FILES =\
	src/lemans.o\
	src/game_loop.o\
	src/hardware_playfield.o\
    src/draw_sprite.o\
    src/draw_status.o\
    src/road_movement.o\
    src/movement_update_inner.o\
    src/track_segments.o\
    src/trackside_items_process.o\
    src/mountains_render.o\
    src/road_render.o\
    src/road_render_fast.o\
    src/player_car.o\
    src/opponent_cars.o\
    src/display_list.o\
    src/detect_collisions.o\
    src/random.o\
    src/hud.o\
    src/time_of_day_process.o\
    src/relocate_sprites.o\
    src/checkpoints.o\
    src/lookups.o\
    src/stars.o\
    src/speedometer.o\
    src/screen_transition.o\
    src/natfeats.o\
    src/generated/trackside_items.o\
 	src/generated/road_geometry.o\
	src/generated/road_graphics.o\
    src/generated/mountain_graphics.o\
    src/generated/new_title_screen_graphics.o\
    src/generated/sprite_definitions.o\
    src/generated/status_definitions.o\
    src/generated/time_of_day.o\
    src/generated/star_lookups.o\
	src/initialise.o\
	src/nf_asmv.o\
	src/mixer_init.o\
	src/mixer_data.o\
	src/mixer_variables.o\
	src/mixer_vbl.o\
	src/play_sound.o\
    src/title_sound.o\
   	src/stars_fast.o\
    src/mountains_render_fast.o\
    src/hardware_playfield_fast.o\
    src/display_list_insertion_sort_fast.o\
    src/music.o\

ASSETS_GIF = assets/round-tree.gif

release/lemans.st: bin/lemans.prg src/boot_sector.bin bin/credits.prg diskcontent/titlefx.raw
	rm release/lemans.st || true
	cp bin/credits.prg diskcontent/AUTO/ || true
	cp bin/lemans.prg diskcontent/AUTO/ || true
	zip -r release/lemans.zip diskcontent/ -x diskcontent/AUTO/.gitkeep -x diskcontent/.gitkeep
	zip2st release/lemans.zip release/lemans.st    
	MTOOLS_NO_VFAT=1 mdel -i release/lemans.st AUTO/credits.prg
	MTOOLS_NO_VFAT=1 mdel -i release/lemans.st AUTO/lemans.prg
	MTOOLS_NO_VFAT=1 mcopy -i release/lemans.st -spmv bin/credits.prg ::/AUTO
	MTOOLS_NO_VFAT=1 mcopy -i release/lemans.st -spmv bin/lemans.prg ::/AUTO
	rm release/lemans.zip || true
	$(PHP) src/apply_boot_sector.php src/boot_sector.bin $@
	@echo "*************************************************************"
	@echo "Build complete. See release/lemans.st for the disk image."
	@echo "*************************************************************"

bin/lemans.prg: $(OBJECT_FILES)
	$(CC)  -o src/lemans.elf libcxx/vsnprint.o libcxx/brownboot.o libcxx/zerolibc.o libcxx/browncrti.o libcxx/browncrtn.o libcxx/browncrt++.o libcxx/zerocrtfini.o $(OBJECT_FILES)  -O3 -Wl,--emit-relocs -Wl,-e_start -Ttext=0 -nostartfiles -m68000 -fomit-frame-pointer -flto -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -fstrict-aliasing -fcaller-saves -ffunction-sections -fdata-sections -fleading-underscore
	brownout -i src/lemans.elf -o bin/lemans.prg
	cp bin/lemans.prg bin/lemans.uncompressed.prg
	$(UPX) bin/lemans.prg
	chmod +x bin/lemans.prg

bin/credits.prg: src/credits.o src/generated/credits_screen_data.o
	$(CC)  -o src/credits.elf libcxx/vsnprint.o libcxx/brownboot.o libcxx/zerolibc.o libcxx/browncrti.o libcxx/browncrtn.o libcxx/browncrt++.o libcxx/zerocrtfini.o src/credits.o src/generated/credits_screen_data.o  -O3 -Wl,--emit-relocs -Wl,-e_start -Ttext=0 -nostartfiles -m68000 -fomit-frame-pointer -flto -D__ATARI__ -D__M68000__ -DELF_CONFIG_STACK=1024 -fstrict-aliasing -fcaller-saves -ffunction-sections -fdata-sections -fleading-underscore
	brownout -i src/credits.elf -o bin/credits.prg
	cp bin/credits.prg bin/credits.uncompressed.prg
	$(UPX) bin/credits.prg
	chmod +x bin/credits.prg

src/generated/credits.o: src/generated/credits.c src/credits.h src/credits_screen_data.h
	$(CC) $(CFLAGS) -c src/generated/credits.c -o src/generated/credits.o

src/generated/credits_screen_data.c: src/generate_credits_screen_data.php src/library.php assets/credits.gif
	$(PHP) src/generate_credits_screen_data.php assets/credits.gif src/generated/credits_screen_data.c

src/generated/credits_screen_data.o: src/generated/credits_screen_data.c src/credits_screen_data.h
	$(CC) $(CFLAGS) -c src/generated/credits_screen_data.c -o src/generated/credits_screen_data.o

src/boot_sector.bin: src/boot_sector.s
	$(VASM) $< -Fbin -o $@

src/lemans.o: src/lemans.c $(OBJECT_FILES)
	$(CC) $(CFLAGS) -c src/lemans.c -o src/lemans.o

src/game_loop.o: src/game_loop.c src/game_loop.h src/hardware_playfield.h src/initialise.h src/road_movement.h src/mountains_render.h src/mountains_render_fast.h src/road_render.h src/player_car.h src/sprite_definitions.h src/road_geometry.h src/trackside_items.h src/display_list.h src/detect_collisions.h src/opponent_cars.h src/time_of_day_process.h src/detect_collisions.h src/mixer_init.h src/hud.h src/music.h src/relocate_sprites.h src/lookups.h src/stars.h src/random.h src/screen_transition.h src/play_sound.h src/title_sound.h src/natfeats.h
	$(CC) $(CFLAGS) -c src/game_loop.c -o src/game_loop.o

src/hardware_playfield.o: src/hardware_playfield.c src/hardware_playfield.h src/blitter.h src/draw_sprite.h src/draw_status.h src/status_definitions.h src/bitplane_draw_record.h src/natfeats.h src/initialise.h src/hud.h src/hud_digits.h src/lookups.h src/player_car.h src/time_of_day_process.h src/stars.h src/hardware_playfield_fast.h
	$(CC) $(CFLAGS) -c src/hardware_playfield.c -o src/hardware_playfield.o

src/draw_sprite.o: src/draw_sprite.s src/draw_sprite.h
	$(VASM) $(VASM_OPTS) src/draw_sprite.s -Felf -o src/draw_sprite.o

src/draw_status.o: src/draw_status.s src/draw_status.h
	$(VASM) $(VASM_OPTS) src/draw_status.s -Felf -o src/draw_status.o

src/road_movement.o: src/road_movement.c src/road_movement.h src/road_geometry.h src/player_car.h src/movement_update_inner.h src/trackside_items.h src/trackside_items_process.h src/play_sound.h src/lookups.h
	$(CC) $(CFLAGS) -c src/road_movement.c -o src/road_movement.o

src/movement_update_inner.o: src/movement_update_inner.s src/movement_update_inner.h
	$(VASM) $(VASM_OPTS) src/movement_update_inner.s -Felf -o src/movement_update_inner.o

src/track_segments.o: src/track_segments.c src/track_segments.h
	$(CC) $(CFLAGS) -c src/track_segments.c -o src/track_segments.o

src/trackside_items_process.o: src/trackside_items_process.c src/trackside_items_process.h src/trackside_items.h src/player_car.h src/sprite_definitions.h src/road_geometry.h src/trackside_items.h src/display_list.h src/lookups.h
	$(CC) $(CFLAGS) -c src/trackside_items_process.c -o src/trackside_items_process.o

src/mountains_render.o: src/mountains_render.c src/mountains_render.h src/mountain_graphics.h src/hardware_playfield.h src/blitter.h src/road_movement.h src/lookups.h src/natfeats.h
	$(CC) $(CFLAGS) -c src/mountains_render.c -o src/mountains_render.o

src/road_render.o: src/road_render.c src/road_render.h src/road_graphics.h src/road_geometry.h src/hardware_playfield.h src/blitter.h src/player_car.h src/road_render_fast.h src/player_car.h src/checkpoints.h
	$(CC) $(CFLAGS) -c src/road_render.c -o src/road_render.o

src/road_render_fast.o: src/road_render_fast.s src/road_render_fast.h src/road_graphics.h src/road_geometry.h src/hardware_playfield.h src/blitter.h
	$(VASM) $(VASM_OPTS) src/road_render_fast.s -Felf -o src/road_render_fast.o

src/player_car.o: src/player_car.c src/player_car.h src/track_segments.h src/initialise.h src/hardware_playfield.h src/checkpoints.h src/hud.h src/play_sound.h src/game_loop.h
	$(CC) $(CFLAGS) -c src/player_car.c -o src/player_car.o

src/opponent_cars.o: src/opponent_cars.c src/opponent_cars.h src/player_car.h src/sprite_definitions.h src/road_geometry.h src/display_list.h src/random.h src/lookups.h src/trackside_items.h src/play_sound.h src/detect_collisions.h
	$(CC) $(CFLAGS) -c src/opponent_cars.c -o src/opponent_cars.o

src/display_list.o: src/display_list.c src/display_list.h src/sprite_definitions.h src/hardware_playfield.h src/display_list_insertion_sort_fast.h
	$(CC) $(CFLAGS) -c src/display_list.c -o src/display_list.o

src/detect_collisions.o: src/detect_collisions.c src/detect_collisions.h src/player_car.h src/trackside_items_process.h src/trackside_items.h src/road_geometry.h src/opponent_cars.h src/play_sound.h src/lookups.h
	$(CC) $(CFLAGS) -c src/detect_collisions.c -o src/detect_collisions.o

src/random.o: src/random.c src/random.h
	$(CC) $(CFLAGS) -c src/random.c -o src/random.o

src/hud.o: src/hud.c src/hud.h src/hud_digits.h src/player_car.h src/hardware_playfield.h
	$(CC) $(CFLAGS) -c src/hud.c -o src/hud.o

src/time_of_day_process.o: src/time_of_day_process.c src/time_of_day_process.h
	$(CC) $(CFLAGS) -c src/time_of_day_process.c -o src/time_of_day_process.o

src/relocate_sprites.o: src/relocate_sprites.c src/relocate_sprites.h src/generated/sprite_definitions_count.h src/natfeats.h
	$(CC) $(CFLAGS) -c src/relocate_sprites.c -o src/relocate_sprites.o

src/checkpoints.o: src/checkpoints.c src/checkpoints.h
	$(CC) $(CFLAGS) -c src/checkpoints.c -o src/checkpoints.o

src/lookups.o: src/lookups.c src/lookups.h src/sprite_definitions.h src/road_geometry.h src/generated/sprite_definitions_count.h
	$(CC) $(CFLAGS) -c src/lookups.c -o src/lookups.o

src/stars.o: src/stars.c src/stars.h src/lookups.h src/star_lookups.h
	$(CC) $(CFLAGS) -c src/stars.c -o src/stars.o

src/speedometer.o: src/speedometer.c src/speedometer.h src/player_car.h src/hardware_playfield.h src/sprite_definitions.h src/lookups.h src/draw_sprite.h
	$(CC) $(CFLAGS) -c src/speedometer.c -o src/speedometer.o

src/screen_transition.o: src/screen_transition.c src/screen_transition.h
	$(CC) $(CFLAGS) -c src/screen_transition.c -o src/screen_transition.o

src/natfeats.o: src/natfeats.c src/natfeats.h
	$(CC) $(CFLAGS) -c src/natfeats.c -o src/natfeats.o

src/generated/trackside_items.c: src/generate_trackside_items.php src/trackside_items_template.php
	$(PHP) src/generate_trackside_items.php src/generated/trackside_items.c

src/generated/trackside_items.o: src/generated/trackside_items.c src/trackside_items.h
	$(CC) $(CFLAGS) -c src/generated/trackside_items.c -o src/generated/trackside_items.o

src/generated/road_geometry.o: src/generated/road_geometry.c src/road_geometry.h
	$(CC) $(CFLAGS) -c src/generated/road_geometry.c -o src/generated/road_geometry.o

src/generated/road_geometry.c: src/generate_road_geometry.php
	$(PHP) src/generate_road_geometry.php src/generated/road_geometry.c

src/generated/road_graphics.o: src/generated/road_graphics.c src/road_graphics.h
	$(CC) $(CFLAGS) -c src/generated/road_graphics.c -o src/generated/road_graphics.o

src/generated/road_graphics.c: src/generate_road_graphics.php
	$(PHP) src/generate_road_graphics.php src/generated/road_graphics.c

src/generated/mountain_graphics.o: src/generated/mountain_graphics.c src/mountain_graphics.h
	$(CC) $(CFLAGS) -c src/generated/mountain_graphics.c -o src/generated/mountain_graphics.o

src/generated/mountain_graphics.c: src/generate_mountain_graphics.php src/library.php assets/mountains.gif
	$(PHP) src/generate_mountain_graphics.php assets/mountains.gif src/generated/mountain_graphics.c

src/generated/new_title_screen_graphics.o: src/generated/new_title_screen_graphics.c src/new_title_screen_graphics.h
	$(CC) $(CFLAGS) -c src/generated/new_title_screen_graphics.c -o src/generated/new_title_screen_graphics.o

src/generated/new_title_screen_graphics.c: src/generate_new_title_screen_graphics.php src/library.php
	$(PHP) src/generate_new_title_screen_graphics.php

src/generated/sprite_definitions.o: src/generated/sprite_definitions.c src/sprite_definitions.h
	$(CC) $(CFLAGS) -c src/generated/sprite_definitions.c -o src/generated/sprite_definitions.o

src/generated/sprite_definitions.c: src/generate_sprite_definitions.php src/library.php src/sprite_spans.php $(ASSETS_GIF) src/sprite_definitions_template.php src/library.php src/sprite_definitions.php
	$(PHP) src/generate_sprite_definitions.php $(ASSETS_GIF) src/generated/sprite_definitions.c

src/generated/sprite_definitions_count.h: src/generate_sprite_definitions_count.php src/sprite_definitions.php
	$(PHP) src/generate_sprite_definitions_count.php src/generated/sprite_definitions_count.h

src/generated/status_definitions.o: src/generated/status_definitions.c src/status_definitions.h
	$(CC) $(CFLAGS) -c src/generated/status_definitions.c -o src/generated/status_definitions.o

src/generated/status_definitions.c: src/generate_status_definitions.php $(ASSETS_GIF) src/status_definitions_template.php src/library.php
	$(PHP) src/generate_status_definitions.php $(ASSETS_GIF) src/generated/status_definitions.c

src/generated/time_of_day.o: src/generated/time_of_day.c src/time_of_day.h
	$(CC) $(CFLAGS) -c src/generated/time_of_day.c -o src/generated/time_of_day.o

src/generated/time_of_day.c: src/generate_time_of_day.php
	$(PHP) src/generate_time_of_day.php src/generated/time_of_day.c

src/generated/star_lookups.c: src/generate_star_lookups.php
	$(PHP) src/generate_star_lookups.php src/generated/star_lookups.c

src/initialise.o: src/initialise.s
	$(VASM) $(VASM_OPTS) -m68030 -no-opt src/initialise.s -Felf -o src/initialise.o

src/mixer_init.o: src/mixer_init.s src/mixer_init.h
	$(VASM) $(VASM_OPTS) src/mixer_init.s -Felf -o src/mixer_init.o

src/mixer_data.o: src/mixer_data.s
	$(VASM) $(VASM_OPTS) src/mixer_data.s -Felf -o src/mixer_data.o

src/mixer_variables.o: src/mixer_variables.s
	$(VASM) $(VASM_OPTS) src/mixer_variables.s -Felf -o src/mixer_variables.o

src/mixer_vbl.o: src/mixer_vbl.s
	$(VASM) $(VASM_OPTS) src/mixer_vbl.s -Felf -o src/mixer_vbl.o

src/play_sound.o: src/play_sound.s
	$(VASM) $(VASM_OPTS) src/play_sound.s -Felf -o src/play_sound.o

src/title_sound.o: src/title_sound.s
	$(VASM) $(VASM_OPTS) src/title_sound.s -Felf -o src/title_sound.o

src/stars_fast.o: src/stars_fast.s
	$(VASM) $(VASM_OPTS) src/stars_fast.s -Felf -o src/stars_fast.o

src/mountains_render_fast.o: src/mountains_render_fast.s
	$(VASM) $(VASM_OPTS) src/mountains_render_fast.s -Felf -o src/mountains_render_fast.o

src/hardware_playfield_fast.o: src/hardware_playfield_fast.s
	$(VASM) $(VASM_OPTS) src/hardware_playfield_fast.s -Felf -o src/hardware_playfield_fast.o

src/display_list_insertion_sort_fast.o: src/display_list_insertion_sort_fast.s
	$(VASM) $(VASM_OPTS) src/display_list_insertion_sort_fast.s -Felf -o src/display_list_insertion_sort_fast.o

src/music.o: src/music.s src/music.h src/jracer.snd
	$(VASM) $(VASM_OPTS) src/music.s -Felf -o src/music.o

src/nf_asmv.o: src/nf_asmv.s
	$(VASM) $(VASM_OPTS) src/nf_asmv.s -Felf -o src/nf_asmv.o

