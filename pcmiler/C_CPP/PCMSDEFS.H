/**************************************************************************
* File:            PCMSDEFS.H
* Revision Number: 17.0
* Copyright:       1995-2003 ALK Technologies, Inc.
* Purpose:         PC*MILER/Connect common defines and types.
*
****************************************************************************/
#ifndef __PCMSDEFS_H
#define __PCMSDEFS_H

#ifndef STRICT
#define STRICT
#endif

#ifndef __WINDOWS_H
#include <windows.h>
#endif


/*********************************************************************
* Defines
*********************************************************************/

/*********************************************************************
* Type of routing calculations
*********************************************************************/

/* Old style route type values */
#define CALC_INVALID        -1
#define CALC_PRACTICAL 	 	0
#define CALC_SHORTEST   	1
#define CALC_NATIONAL   	2
#define CALC_AVOIDTOLL  	3
#define CALC_AIR			4
#define CALC_FIFTYTHREE 	6

/* New style route type values */
#define CALCEX_TYPE_PRACTICAL   1
#define CALCEX_TYPE_SHORTEST    2
#define CALCEX_TYPE_AIR		    4
#define CALCEX_OPT_AVOIDTOLL    256
#define CALCEX_OPT_NATIONAL	    512
#define CALCEX_OPT_FIFTYTHREE   1024
#define CALCEX_VEH_TRUCK        0
#define CALCEX_VEH_AUTO         16777216

/*********************************************************************
* Report types
*********************************************************************/
#define RPT_DETAIL      0
#define RPT_STATE       1
#define RPT_MILEAGE     2
#define RPT_XML         3

/*********************************************************************
* Toll Mode types
*********************************************************************/
#define TOLL_NONE         0
#define TOLL_CASH         1
#define TOLL_DISCOUNT     2

/*********************************************************************
* Order of states in reports
*********************************************************************/
#define STATE_ORDER     1
#define TRIP_ORDER      2

/*********************************************************************
* Options
*********************************************************************/
#define OPTS_NONE       0x0000L
#define OPTS_MILES      0x0001L
#define OPTS_CHANGEDEST 0x0002L
#define OPTS_HUBMODE    0x0004L
#define OPTS_BORDERS    0x0008L
#define OPTS_ALPHAORDER 0x0010L
#define OPTS_HEAVY      0x0020L
#define OPTS_ERROR      0xFFFFL

/*********************************************************************
* Error codes
*********************************************************************/
#define PCMS_INVALIDPTR      101
#define PCMS_NOINIFILE       102
#define PCMS_LOADINIFILE     103
#define PCMS_LOADGEOCODE     104
#define PCMS_LOADNETWORK     105
#define PCMS_MAXTRIPS        106
#define PCMS_INVALIDTRIP     107
#define PCMS_INVALIDSERVER   108
#define PCMS_BADROOTDIR      109
#define PCMS_BADMETANETDIR   110
#define PCMS_NOLICENSE       111
#define PCMS_TRIPNOTREADY    112
#define PCMS_INVALIDPLACE    113
#define PCMS_ROUTINGERROR    114
#define PCMS_OPTERROR        115
#define PCMS_OPTHUB          116
#define PCMS_OPT2STOPS       117
#define PCMS_OPT3STOPS       118
#define PCMS_NOTENOUGHSTOPS  119
#define PCMS_BADNETDIR       120
#define PCMS_LOADGRIDNET     121
#define PCMS_BADOPTIONDIR    122
#define PCMS_DISCONNECTEDNET 123
#define PCMS_NOTRUCKSTOP     124
#define PCMS_INVALIDREGIONID 125
#define PCMS_CLOSINGERROR    126
#define PCMS_NORTENGINE      127
#define PCMS_NODATASERVER    128
#define PCMS_NOACTIVATE      135
#define PCMS_EXPIRED         136
#define PCMS_BADDLLPATH      137

/*********************************************************************
* Detailed driving instruction in a structured format
*********************************************************************/
struct segmentStruct{
	char stateAbbrev[2];
	int  toll;
	char dir[2];          /*  implies N, S, E, W; additional character for future use  */
	char route[32];
	int miles;            /*  includes implied decimal  */
	int minutes;
	char interchange[32];
};

struct legInfoType
{
	float legMiles;
	float totMiles;
	float legCost;
	float totCost;
	float legHours;
	float totHours;
};

#endif	/* __PCMSDEFS_H */

