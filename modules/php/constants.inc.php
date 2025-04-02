<?php

/*
 * Game options
 */
const OPTION_VARIANT = 110;
const OPTION_VARIANT_FIRST_GAME = 0;
const OPTION_VARIANT_BASE = 1;
const OPTION_VARIANT_ADVANCED = 2;
const OPTION_VARIANT_SCENARIO = 3;

const OPTION_SCENARIO = 111;

/*
 * State constants
 */
const ST_GAME_SETUP = 1;
const ST_GENERIC_STATE = 2;
const ST_END_GAME = 99;


/*
 * BOARDS
 */
const WATER = 100;
const PINK_CIRCLE = 0;
const RED_CIRCLE = 1;
const BLUE_CIRCLE = 2;
const WHITE_CIRCLE = 3;
const PINK_SQUARE = 4;
const RED_SQUARE = 5;
const BLUE_SQUARE = 6;
const WHITE_SQUARE = 7;

// Board rotated so that icon is in top left position
const BOARDS = [
  PINK_CIRCLE => [
    [0, 0, 1],
    [2, 3, 1],
    [2, 3, 1]
  ],
  RED_CIRCLE => [
    [0, 1, 1],
    [0, WATER, 1],
    [2, 2, 2]
  ],
  BLUE_CIRCLE => [
    [0, 0, 1],
    [0, 0, 1],
    [2, 2, WATER],
  ],
  WHITE_CIRCLE => [
    [0, 1, 1],
    [0, 0, 2],
    [0, WATER, 2]
  ],
  PINK_SQUARE => [
    [0, 1, 1],
    [0, 1, 2],
    [1, 1, 2]
  ],
  RED_SQUARE => [
    [0, 0, 0],
    [0, WATER, 1],
    [0, 1, 1]
  ],
  BLUE_SQUARE => [
    [0, 0, 0],
    [0, 1, 1],
    [1, 1, WATER],
  ],
  WHITE_SQUARE => [
    [0, 0, 0],
    [1, 2, WATER],
    [1, 2, 2]
  ],
];


const NW = 0;
const NE = 1;
const SE = 2;
const SW = 3;

const FIRST_GAME_BOARDS = [
  [BLUE_CIRCLE, SE],
  [PINK_CIRCLE, SW],
  [RED_CIRCLE, NW],
  [WHITE_CIRCLE, NE],
];

const SCENARIOS = [
  // A
  [
    'cards' => [2, 3, 4, 13, 22],
    'boards' => [
      [WHITE_CIRCLE, SW],
      [BLUE_CIRCLE, SW],
      [PINK_CIRCLE, SW],
      [RED_CIRCLE, NW],
    ],
    'score' => 75
  ],
  // B
  [
    'cards' => [6, 7, 9, 15, 17],
    'boards' => [
      [RED_CIRCLE, NE],
      [BLUE_SQUARE, SW],
      [WHITE_CIRCLE, NW],
      [PINK_SQUARE, NW],
    ],
    'score' => 80
  ],
  // C
  [
    'cards' => [3, 5, 8, 12, 24],
    'boards' => [
      [BLUE_CIRCLE, NW],
      [WHITE_CIRCLE, SE],
      [PINK_CIRCLE, NW],
      [RED_CIRCLE, NE],
    ],
    'score' => 85
  ],
  // D
  [
    'cards' => [1, 10, 12, 15, 21],
    'boards' => [
      [WHITE_CIRCLE, NE],
      [RED_CIRCLE, SW],
      [BLUE_CIRCLE, NE],
      [PINK_CIRCLE, SE],
    ],
    'score' => 90
  ],
  // E
  [
    'cards' => [2, 6, 15, 22, 23],
    'boards' => [
      [PINK_CIRCLE, NE],
      [BLUE_SQUARE, SE],
      [WHITE_SQUARE, NW],
      [RED_CIRCLE, SE],
    ],
    'score' => 95
  ],
  // F
  [
    'cards' => [1, 13, 20, 21, 24],
    'boards' => [
      [PINK_CIRCLE, NE],
      [WHITE_CIRCLE, SE],
      [RED_SQUARE, SE],
      [BLUE_CIRCLE, SW],
    ],
    'score' => 100
  ],
  // G
  [
    'cards' => [4, 8, 11, 16, 18],
    'boards' => [
      [BLUE_CIRCLE, NW],
      [PINK_CIRCLE, NW],
      [WHITE_CIRCLE, SE],
      [RED_CIRCLE, SW],
    ],
    'score' => 105
  ],

  // H
  [
    'cards' => [3, 10, 21, 22, 23],
    'boards' => [
      [BLUE_CIRCLE, SE],
      [RED_CIRCLE, NE],
      [PINK_CIRCLE, SE],
      [WHITE_SQUARE, NW],
    ],
    'score' => 110
  ],
  // I
  [
    'cards' => [7, 9, 10, 19, 21],
    'boards' => [
      [BLUE_CIRCLE, SW],
      [WHITE_SQUARE, SE],
      [RED_CIRCLE, SE],
      [PINK_CIRCLE, NW],
    ],
    'score' => 115
  ],
  // J
  [
    'cards' => [6, 8, 16, 17, 18],
    'boards' => [
      [RED_SQUARE, NW],
      [BLUE_SQUARE, NE],
      [WHITE_SQUARE, SE],
      [PINK_SQUARE, SW],
    ],
    'score' => 120
  ],
  // K
  [
    'cards' => [2, 8, 14, 17, 20],
    'boards' => [
      [BLUE_SQUARE, NW],
      [RED_SQUARE, SW],
      [PINK_CIRCLE, NE],
      [WHITE_CIRCLE, SE],
    ],
    'score' => 125
  ],
  // L
  [
    'cards' => [5, 6, 9, 14, 17],
    'boards' => [
      [WHITE_SQUARE, NE],
      [RED_SQUARE, SW],
      [BLUE_CIRCLE, SE],
      [PINK_SQUARE, SE],
    ],
    'score' => 130
  ],
  // M
  [
    'cards' => [4, 7, 11, 12, 13],
    'boards' => [
      [PINK_SQUARE, SE],
      [WHITE_SQUARE, SW],
      [RED_SQUARE, NW],
      [BLUE_SQUARE, NE],
    ],
    'score' => 135
  ],
  // N
  [
    'cards' => [12, 14, 18, 19, 23],
    'boards' => [
      [BLUE_CIRCLE, NW],
      [PINK_SQUARE, SE],
      [RED_SQUARE, NW],
      [WHITE_CIRCLE, SW],
    ],
    'score' => 150
  ],
];
