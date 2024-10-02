    public _display_list_insertion_sort_fast:

_display_list_insertion_sort_fast:

    move.l sp,a0
    movem.l d2-d4/a2,-(sp)

    move.w 6(a0),d2 ; number of items
    lea _display_list,a0

    moveq.l #1,d0 ; d0 corresponds to i variable
    lea 6(a0),a1 ; a1 will be current key address (starting at arr[1])

_i_iteration:
    cmp.w d2,d0 ; compare i with n (d0 with d2)
    bge.s _exitloop

    ; key = arr[i]
    move.l (a1)+,d3 ; get first longword of key
    move.w (a1)+,d4 ; get second longword of key

    ; 16 rather than 8 because a1 already advanced by 8
    lea -12(a1),a2 ; j = i - 1

_j_iteration:
    cmp.l a0,a2 ; is j < 0?
    blt.s _j_terminated

    ; ypos is at offset 4 from a1
    cmp.w 4(a2),d4 ; compare key.ypos (d4) to arr[j].ypos 6(a2)
    blt.s _j_terminated

    move.l (a2),6(a2)    ; arr[j + 1] = arr[j]
    move.w 4(a2),10(a2)
    subq.l #6,a2         ; j = j - 1

    bra.s _j_iteration

_j_terminated:

    move.l d3,6(a2)   ; arr[j + 1] = key
    move.w d4,10(a2)
    
    ; move i to next element
    addq.l #1,d0

    bra.s _i_iteration

_exitloop:

    movem.l (sp)+,d2-d4/a2

    rts
