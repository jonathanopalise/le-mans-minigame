    public _movement_update_inner_scenario_2
    public _movement_update_inner_scenario_1

_movement_update_inner_scenario_1:

    move.l sp,a0

    ; should be in this order with sp going upwards:
    ;            sizeof(struct RoadScanline),                                        // needs to go into a2
    ;            &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
    ;           &(current_road_scanline->logical_xpos_add_values[shift_required]   // needs to go into a1

    move 6(a0),a2      ; sizeof(struct RoadScanline)
    move.l 12(a0),a1
    move.l 8(a0),a0

    rept 100
    move.l (a1),d0     ; current_road_scanline->logical_xpos_add_values[shift_required]
    add.l d0,(a0)      ; current_logical_xpos+=(above value)
    add.l a2,a0          ; increment by sizeof(RoadScanline)
    add.l a2,a1          ; increment by sizeof(RoadScanline)
    endr
    
    rts

_movement_update_inner_scenario_2:

    move.l sp,a0

    ; should be in this order with sp going upwards:
    ;            sizeof(struct RoadScanline),                                        // needs to go into a2
    ;            &(current_road_scanline->current_logical_xpos),                     // needs to go into a0
    ;           &(current_road_scanline->logical_xpos_add_values[shift_required]   // needs to go into a1

    move 6(a0),a2      ; sizeof(struct RoadScanline)
    move.l 12(a0),a1
    move.l 8(a0),a0

    rept 100
    move.l (a1),d0     ; current_road_scanline->logical_xpos_add_values[shift_required]
    sub.l d0,(a0)      ; current_logical_xpos+=(above value)
    add.l a2,a0          ; increment by sizeof(RoadScanline)
    add.l a2,a1          ; increment by sizeof(RoadScanline)
    endr
    
    rts


