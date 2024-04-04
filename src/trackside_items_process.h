#ifndef __TRACKSIDE_ITEMS_PROCESS_H
#define __TRACKSIDE_ITEMS_PROCESS_H

extern struct TracksideItem *current_nearest_trackside_item;

void trackside_items_process_init();
void trackside_items_update_nearest();
void trackside_items_process();

#endif
