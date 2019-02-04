-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: attack-db
-- Generation Time: Jan 24, 2017 at 12:33 PM
-- Server version: 10.1.20-MariaDB-1~jessie
-- PHP Version: 5.6.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attack`
--

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `lastname`, `login`) VALUES
(-1, 'NEUTRAL_COUNTRY', 'NEUTRAL_COUNTRY', 'NEUTRAL_COUNTRY');

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `name`) VALUES
(1, 'land'),
(2, 'sea'),
(3, 'air');

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `name`, `number`, `coords_small`, `x`, `y`, `x2`, `y2`, `xres`, `yres`, `height`, `width`, `id_type`, `economy`) VALUES
(1, 'Alaska', 1, '7,45,55,40,70,41,78,11,42,7', 8, 6, 0, 0, 27, 12, 42, 71, 1, 'poor'),
(2, 'Northwest Territories', 2, '71,41,79,12,122,4,165,8,145,29,178,25,142,54', 71, 5, 0, 0, 81, 20, 50, 106, 1, 'weak'),
(3, 'British Columbia', 3, '63,42,102,50,86,89,63,85', 58, 43, 0, 0, 63, 48, 46, 43, 1, 'normal'),
(4, 'Alberta', 4, '103,50,139,58,150,70,136,91,90,88', 90, 51, 0, 0, 102, 55, 40, 61, 1, 'weak'),
(5, 'Ontario', 5, '137,91,153,71,166,74,176,83,189,103,170,105', 137, 72, 0, 0, 148, 72, 34, 56, 1, 'normal'),
(6, 'Quebec', 6, '179,83,195,103,224,101,233,88,252,85,175,47', 176, 48, 0, 0, 185, 58, 57, 76, 1, 'normal'),
(7, 'The Western United States', 7, '61,91,89,92,85,137,96,151,64,144,52,114', 53, 90, 0, 0, 54, 94, 62, 44, 1, 'normal'),
(8, 'The Great Plains', 8, '93,91,124,94,119,136,130,162,122,170,87,137', 89, 92, 0, 0, 93, 96, 78, 42, 1, 'normal'),
(9, 'The Midwest', 9, '128,94,135,95,168,107,168,135,123,133', 124, 95, 0, 0, 130, 97, 40, 45, 1, 'strong'),
(10, 'New England', 10, '171,108,210,105,188,143,171,135', 170, 105, 0, 0, 173, 107, 37, 38, 1, 'strong'),
(11, 'The Old South', 11, '123,137,169,139,185,144,179,157,184,176,170,156,132,159', 123, 137, 0, 0, 130, 138, 38, 62, 1, 'strong'),
(12, 'Mexico', 12, '66,148,101,155,119,174,116,189,90,197,73,179', 64, 147, 0, 0, 73, 155, 52, 55, 1, 'weak'),
(13, 'Antilles-Panama', 13, '97,200,118,193,123,197,156,180,171,183,206,204,183,201,155,187,137,198,140,207,130,224,119,210', 97, 181, 0, 0, 118, 194, 43, 106, 1, 'weak'),
(14, 'Colombia', 14, '135,227,174,226,189,233,136,260,131,248,132,231', 131, 226, 0, 0, 143, 228, 37, 59, 1, 'poor'),
(15, 'Peru', 15, '129,251,147,307,139,313,111,289,114,257', 111, 250, 0, 0, 114, 258, 67, 39, 1, 'poor'),
(16, 'Amazonia', 16, '137,264,194,235,235,267,201,298,144,283', 138, 235, 0, 0, 158, 254, 64, 98, 1, 'poor'),
(17, 'Brazil', 17, '191,336,203,301,239,270,250,275,233,314,213,319,206,340', 190, 269, 0, 0, 211, 293, 74, 59, 1, 'weak'),
(18, 'Bolivia', 18, '145,287,198,301,188,334,151,308', 145, 288, 0, 0, 155, 291, 48, 56, 1, 'poor'),
(19, 'Chile', 19, '150,312,168,324,180,366,165,374,142,318', 144, 312, 0, 0, 148, 317, 64, 37, 1, 'poor'),
(20, 'Argentina', 20, '127,327,188,340,205,346,194,397,176,396,168,378,184,366', 166, 328, 0, 0, 181, 342, 71, 41, 1, 'weak'),
(21, 'Iceland-Great Britain', 21, '309,58,317,68,324,66,334,88,328,95,310,99,297,94,296,84,313,76,314,68,308,64,292,64,290,58,294,56', 290, 56, 0, 0, 303, 70, 43, 44, 1, 'strong'),
(22, 'Norway-Sweden', 22, '391,30,384,46,370,60,368,78,356,69,346,73,336,62,370,36', 336, 30, 0, 0, 351, 41, 51, 56, 1, 'strong'),
(23, 'Finland-Karelia', 23, '396,28,435,44,430,71,383,66', 381, 27, 0, 0, 396, 31, 45, 55, 1, 'normal'),
(24, 'Uralsk', 24, '432,73,440,44,448,26,464,23,453,30,489,40,482,96,445,88', 433, 40, 0, 0, 441, 44, 57, 55, 1, 'strong'),
(25, 'France-Spain', 25, '340,94,348,124,339,129,342,134,338,138,334,130,330,133,330,140,315,146,302,144,305,122,324,119,318,106', 300, 94, 0, 0, 314, 112, 54, 50, 1, 'strong'),
(26, 'Germany-Italy', 26, '342,90,350,86,347,78,352,76,356,86,374,84,384,135,371,149,365,144,376,138,360,127,356,139,354,138,352,123', 341, 76, 0, 0, 350, 91, 74, 42, 1, 'strong'),
(27, 'Poland-The Balkans', 27, '378,84,388,74,412,132,398,136,401,148,394,149,388,135', 378, 75, 0, 0, 380, 85, 73, 34, 1, 'normal'),
(28, 'Ukraine', 28, '389,69,430,75,442,90,426,110,426,116,419,116,418,110,411,118', 385, 69, 0, 0, 398, 72, 51, 58, 1, 'strong'),
(29, 'Caucasus', 29, '444,92,499,102,478,118,466,118,462,124,464,130,458,135,452,127,434,118', 429, 92, 0, 0, 443, 93, 44, 70, 1, 'normal'),
(30, 'Turkey-Syria', 30, '452,132,480,182,436,182,428,172,434,154,424,156,423,150,410,149,408,138,424,131', 408, 130, 0, 0, 434, 134, 54, 75, 1, 'weak'),
(31, 'Saudia Arabia', 31, '442,188,482,186,492,196,500,190,513,198,492,216,482,216,470,226', 441, 187, 0, 0, 458, 188, 40, 73, 1, 'weak'),
(32, 'Algeria', 32, '297,176,310,151,360,146,349,190', 298, 146, 0, 0, 310, 156, 46, 64, 1, 'weak'),
(33, 'Libya', 33, '357,161,391,171,392,162,401,166,396,204,352,192', 352, 161, 0, 0, 361, 167, 44, 50, 1, 'weak'),
(34, 'Egypt-Sudan', 34, '404,168,422,173,441,207,434,239,426,242,400,207', 399, 169, 0, 0, 406, 178, 73, 43, 1, 'weak'),
(35, 'Somaliland', 35, '444,210,465,234,480,239,463,272,436,241', 435, 211, 0, 0, 446, 226, 62, 47, 1, 'poor'),
(36, 'French West Africa', 36, '296,179,362,198,370,217,313,226,311,252,300,253,274,226,282,193', 274, 179, 0, 0, 290, 195, 76, 97, 1, 'poor'),
(37, 'Nigeria', 37, '316,230,372,220,380,240,360,264,352,253,314,253', 314, 221, 0, 0, 326, 228, 48, 67, 1, 'poor'),
(38, 'French Equatorial Africa', 38, '366,199,396,208,425,244,424,296,368,282,360,268,384,242', 357, 200, 0, 0, 386, 216, 97, 68, 1, 'poor'),
(39, 'Kenya', 39, '428,244,434,242,459,276,458,304,428,297', 428, 243, 0, 0, 433, 256, 63, 34, 1, 'poor'),
(40, 'Angola', 40, '369,286,412,296,378,343,368,320,374,304', 367, 285, 0, 0, 371, 290, 62, 47, 1, 'poor'),
(41, 'South Africa', 41, '416,298,458,308,459,316,440,326,440,340,421,366,388,374,380,348', 379, 298, 0, 0, 405, 320, 77, 81, 1, 'weak'),
(42, 'Madagascar', 42, '465,318,480,305,480,338,472,351,464,351,460,340,464,333', 460, 305, 0, 0, 469, 307, 47, 23, 1, 'poor'),
(43, 'Western Sibir', 43, '492,31,554,10,576,16,540,108,485,96', 485, 11, 0, 0, 500, 38, 98, 101, 1, 'normal'),
(44, 'Central Sibir', 44, '576,26,615,25,636,75,615,82,593,82,566,50', 566, 22, 0, 0, 579, 28, 61, 72, 1, 'weak'),
(45, 'Eastern Sibir', 45, '618,21,682,13,692,52,651,78,640,76', 614, 12, 0, 0, 632, 27, 66, 80, 1, 'weak'),
(46, 'Buryat', 46, '654,80,694,55,708,56,722,85,706,93', 655, 56, 0, 0, 685, 65, 38, 69, 1, 'weak'),
(47, 'Outer Mongolia', 47, '618,86,638,78,704,96,697,128,640,120', 617, 78, 0, 0, 632, 82, 50, 87, 1, 'poor'),
(48, 'Southern Sibir', 48, '565,52,590,85,581,152,542,110', 542, 51, 0, 0, 559, 71, 101, 50, 1, 'normal'),
(49, 'Kazakhstan', 49, '475,126,503,104,540,112,566,142,556,157,490,149', 475, 104, 0, 0, 497, 113, 54, 93, 1, 'normal'),
(50, 'Sinkiang', 50, '594,86,613,86,637,122,629,152,604,150,593,166,582,155', 582, 86, 0, 0, 596, 104, 81, 56, 1, 'poor'),
(51, 'West China', 51, '640,124,706,132,676,158,632,152', 632, 125, 0, 0, 645, 128, 34, 75, 1, 'poor'),
(52, 'Iran', 52, '460,138,464,134,478,156,486,152,554,160,533,191,506,190,500,180,494,184,480,168,476,168', 460, 133, 0, 0, 496, 154, 62, 96, 1, 'weak'),
(53, 'West India', 53, '538,190,569,149,599,178,574,216,566,208,562,215', 538, 145, 0, 0, 557, 166, 74, 62, 1, 'weak'),
(54, 'Tibet', 54, '594,169,605,154,674,160,672,190,602,178', 594, 154, 0, 0, 609, 154, 36, 81, 1, 'poor'),
(55, 'East India', 55, '576,220,602,180,655,190,650,204,632,209,610,252,622,271,614,274,607,260,598,260', 575, 181, 0, 0, 599, 192, 93, 81, 1, 'weak'),
(56, 'Yakut', 56, '688,10,750,14,764,54,739,54,696,52', 688, 8, 0, 0, 695, 14, 47, 78, 1, 'poor'),
(57, 'Koryak', 57, '754,13,826,20,828,29,799,45,784,48,799,73,792,85,776,70', 753, 12, 0, 0, 765, 16, 73, 73, 1, 'poor'),
(58, 'Vladivostock', 58, '711,56,737,58,734,70,749,67,768,85,768,90,758,82,762,99,761,111,755,111,725,84', 711, 57, 0, 0, 722, 58, 55, 62, 1, 'weak'),
(59, 'Manchukuo-Korea', 59, '707,96,724,88,750,110,749,127,762,136,755,146,739,128,730,132,700,128', 700, 88, 0, 0, 710, 98, 59, 62, 1, 'weak'),
(60, 'Japan', 60, '776,100,788,100,796,97,795,106,790,117,800,134,788,148,779,151,776,174,770,164,766,146,782,126', 767, 100, 0, 0, 781, 104, 72, 33, 1, 'normal'),
(61, 'East China', 61, '686,153,710,132,736,138,734,146,750,158,748,179,744,187', 686, 133, 0, 0, 705, 140, 54, 63, 1, 'weak'),
(62, 'South China', 62, '678,160,684,155,742,190,734,206,708,210,707,216,674,191', 675, 155, 0, 0, 680, 164, 62, 68, 1, 'weak'),
(63, 'South-East Asia', 63, '659,190,672,193,706,218,720,238,720,247,708,258,692,240,688,254,706,273,710,288,698,281,690,263,682,262,676,226,672,232,667,231,653,204', 654, 191, 0, 0, 662, 195, 99, 68, 1, 'poor'),
(64, 'Marianas Islands', 64, '804,188,811,197,817,198,822,210,827,211,826,214,823,214,819,230,817,228,816,222,819,218,814,203,808,202,802,191', 803, 190, 0, 0, 806, 194, 37, 25, 1, 'poor'),
(65, 'Philippine Islands', 65, '750,219,758,220,761,225,760,232,767,235,779,254,778,269,769,264,762,264,758,248,749,258,747,256,754,238,748,228', 747, 219, 0, 0, 748, 217, 51, 33, 1, 'poor'),
(66, 'Carolini Islands', 66, '808,242,846,244,876,256,875,258,849,257,822,254,808,250,800,255,798,253,799,249', 799, 240, 0, 0, 814, 237, 18, 79, 1, 'poor'),
(67, 'Dutch East Indies', 67, '726,283,738,272,747,268,754,268,752,276,760,280,779,276,782,278,781,285,764,307,758,304,758,288,753,288,744,304,732,300', 725, 266, 0, 0, 738, 265, 41, 60, 1, 'weak'),
(68, 'New Guinea', 68, '790,281,805,282,822,284,838,302,830,304,824,298,816,303,808,302,807,294,792,292', 782, 279, 0, 0, 794, 274, 27, 59, 1, 'poor'),
(69, 'Solomon Islands', 69, '836,276,872,291,886,300,891,308,889,308,886,304,876,302,860,297,837,282', 825, 274, 0, 0, 840, 271, 36, 68, 1, 'poor'),
(70, 'Indonesia', 70, '684,274,687,274,697,286,700,286,710,296,716,307,727,308,742,314,770,314,777,306,779,308,774,316,760,321,749,320,711,314,710,308,700,304,690,290', 681, 269, 0, 0, 693, 279, 54, 101, 1, 'weak'),
(71, 'Northern Territory', 71, '765,340,779,320,792,311,806,314,803,320,806,324,806,348,780,348', 765, 311, 0, 0, 779, 314, 38, 42, 1, 'weak'),
(72, 'Queensland', 72, '809,326,815,327,816,314,820,306,822,310,821,316,826,316,828,328,843,348,842,364,835,369,808,350', 808, 307, 0, 0, 814, 317, 62, 35, 1, 'normal'),
(73, 'Western Australia', 73, '762,342,778,350,772,377,753,388,746,386,748,378,744,368,746,352,750,346', 744, 342, 0, 0, 752, 343, 46, 33, 1, 'weak'),
(74, 'New South Wales', 74, '780,350,807,352,833,370,826,382,822,384,821,390,816,400,808,400,800,380,794,380,788,370,775,376', 776, 351, 0, 0, 785, 352, 49, 58, 1, 'normal'),
(75, 'New Zealand', 75, '894,348,910,358,909,360,865,398,856,398,856,394,894,359,892,348', 855, 348, 0, 0, 869, 365, 53, 56, 1, 'normal'),
(76, 'Aleutian Islands', 76, '813,70,838,63,918,20,938,1,944,1,923,22,898,36,864,55,834,68,814,72', 811, 1, 0, 0, 890, 30, 72, 153, 1, 'poor'),
(77, 'Midway Island', 77, '881,102,883,102,886,106,892,106,903,112,906,120,904,120,892,109,887,110,881,106', 881, 103, 0, 0, 889, 98, 28, 36, 1, 'poor'),
(78, 'Hawaii', 78, '933,138,951,146,962,155,952,164,950,154,934,143,931,138', 929, 138, 0, 0, 936, 133, 26, 33, 1, 'poor'),
(79, 'Marshall Islands', 79, '886,212,905,211,912,218,915,237,906,239,891,217,884,216,884,212', 883, 211, 0, 0, 899, 205, 29, 33, 1, 'poor'),
(80, 'Gilbert Islands', 80, '944,258,946,258,950,272,952,272,952,290,950,290,947,280,944,268', 944, 259, 0, 0, 948, 248, 32, 10, 1, 'poor'),
(81, 'Gulf of Alaska', 81, '1,18,27,17,10,28,10,37,2,42,3,55,17,52,30,42,46,43,56,48,55,65,59,82,48,108,1,96', 3, 42, 0, 0, 13, 61, 65, 56, 2, 'none'),
(82, 'Cape Mendocino', 82, '1,98,50,112,52,128,1,150', 3, 102, 0, 0, 957, 102, 45, 49, 2, 'none'),
(83, 'Hudson Bay', 83, '142,58,182,30,200,42,220,58,219,62,202,52,171,43,170,52,174,79,168,72', 144, 28, 0, 0, 167, 27, 51, 75, 2, 'none'),
(84, 'Gulf of California', 84, '1,153,56,130,68,179,1,179', 3, 132, 0, 0, 15, 146, 47, 63, 2, 'none'),
(85, 'Gulf of Tehuantepec', 85, '22,182,76,182,78,198,94,204,98,208,122,216,36,228,22,216', 24, 185, 0, 0, 30, 193, 40, 97, 2, 'none'),
(86, 'Gulf of Mexico', 86, '118,182,122,178,126,170,140,160,170,161,172,170,179,178,184,178,184,188,172,180,155,178,131,187,122,195', 119, 160, 0, 0, 132, 163, 32, 64, 2, 'none'),
(87, 'Caribbean Sea', 87, '138,200,152,188,193,208,208,208,210,200,217,200,202,239,198,233,174,223,146,222,140,226,134,217,142,212,142,203', 135, 190, 0, 0, 153, 195, 45, 75, 2, 'none'),
(88, 'Pacific Galapagos', 88, '22,220,36,230,122,220,132,243,110,258,107,276,110,294,22,296', 24, 222, 0, 0, 46, 248, 71, 107, 2, 'none'),
(89, 'Pacific San Felix', 89, '56,298,117,298,125,310,150,329,152,354,72,354', 60, 301, 0, 0, 80, 318, 52, 90, 2, 'none'),
(90, 'South Pacific Center', 90, '22,299,53,299,81,399,21,399', 24, 301, 0, 0, 24, 367, 96, 55, 2, 'none'),
(91, 'Cape Horn', 91, '72,358,156,358,176,399,84,399', 75, 359, 0, 0, 92, 371, 38, 97, 2, 'none'),
(92, 'Norwegian Sea', 92, '308,24,346,14,360,40,348,50,298,56,292,46,310,34', 296, 17, 0, 0, 300, 23, 38, 62, 2, 'none'),
(93, 'North Sea', 93, '312,57,337,54,334,64,346,84,338,89,318,102,311,100,334,88', 316, 58, 0, 0, 325, 69, 43, 29, 2, 'none'),
(94, 'Irish Sea', 94, '270,56,290,48,292,54,288,57, 291,65,304,66,312,60,315,65,309,70,312,77,296,84,295,88,272,96', 271, 54, 0, 0, 270, 64, 39, 41, 2, 'none'),
(95, 'Labrador Sea', 95, '240,52,246,53,248,61,254,64,266,57,270,96,261,116,232,92,240,88,254,88,252,80,246,74,232,66,222,62,224,60,230,64,235,60', 235, 56, 0, 0, 243, 57, 59, 33, 2, 'none'),
(96, 'Bay of Biscay', 96, '278,98,294,92,295,95,320,108,322,118,257,132', 261, 95, 0, 0, 271, 104, 35, 60, 2, 'none'),
(97, 'Gulf of Maine', 97, '218,100,224,104,232,95,260,119,241,161,196,126', 200, 98, 0, 0, 214, 117, 62, 58, 2, 'none'),
(98, 'Mediterranean Sea West', 98, '332,132,354,123,372,138,364,143,372,152,386,138,395,153,376,166,358,158,362,154,361,145,310,150,310,147,330,141', 314, 117, 0, 0, 336, 129, 46, 79, 2, 'none'),
(99, 'North Atlantic Canary', 99, '256,134,301,126,300,144,307,148,306,154,304,162,280,192,230,196', 233, 129, 0, 0, 257, 154, 64, 73, 2, 'none'),
(100, 'North Atlantic Cape Verde', 100, '254,196,280,194,272,225,282,239,252,250', 254, 197, 0, 0, 254, 210, 50, 24, 2, 'none'),
(101, 'North Atlantic Antilles', 101, '220,199,252,196,250,250,224,259,216,252,206,252,203,243', 205, 199, 0, 0, 216, 217, 60, 44, 2, 'none'),
(102, 'Sargasso Sea', 102, '198,132,240,166,226,196,198,198,186,190,186,174,180,155', 183, 136, 0, 0, 196, 159, 60, 54, 2, 'none'),
(103, 'Ivory Coast', 103, '266,248,284,242,287,246,302,256,335,250,338,252,313,300', 270, 244, 0, 0, 292, 258, 53, 65, 2, 'none'),
(104, 'South Atlantic Guiana', 104, '228,261,264,248,312,303,304,316,241,296,252,272', 235, 250, 0, 0, 253, 268, 65, 75, 2, 'none'),
(105, 'South Atlantic Rio de la Plata', 105, '240,297,302,320,275,370,196,366,204,358,210,342,208,337,216,322,236,316', 199, 301, 0, 0, 236, 328, 68, 102, 2, 'none'),
(106, 'South Atlantic Namib', 106, '298,334,368,326,384,366,385,374,279,370', 282, 328, 0, 0, 313, 339, 45, 101, 2, 'none'),
(107, 'Gulf of Guinea', 107, '341,255,344,257,354,256,356,260,354,264,356,276,365,284,370,302,366,322,300,331', 303, 259, 0, 0, 326, 292, 71, 65, 2, 'none'),
(108, 'South Atlantic Falkland', 108, '194,369,254,372,255,399,196,399', 194, 372, 0, 0, 206, 378, 26, 58, 2, 'none'),
(109, 'Scotia Sea', 109, '258,372,343,376,341,399,258,399', 259, 375, 0, 0, 275, 380, 22, 82, 2, 'none'),
(110, 'Barent Sea', 110, '350,14,382,11,414,14,414,33,397,26,384,28,362,40', 355, 14, 0, 0, 364, 11, 22, 58, 2, 'none'),
(111, 'Baltic Sea', 111, '372,60,384,50,385,56,381,60,380,66,386,67,379,82,369,82,378,68,372,64', 375, 59, 0, 0, 371, 63, 21, 9, 2, 'none'),
(112, 'Black Sea', 112, '412,116,414,113,418,114,416,117,422,122,430,118,442,126,450,128,448,132,425,129,418,132,410,126,411,120', 413, 114, 0, 0, 417, 112, 17, 28, 2, 'none'),
(113, 'Mediterranean Sea East', 113, '378,168,398,154,406,156,411,154,412,152,424,152,422,154,423,157,427,158,434,154,430,160,428,170,414,173,400,161,392,162,391,170', 382, 138, 0, 0, 404, 147, 31, 45, 2, 'none'),
(114, 'Red Sea', 114, '428,184,432,180,436,184,444,192,448,200,448,204,462,218,468,228,480,222,480,235,468,232,443,208', 433, 185, 0, 0, 434, 190, 47, 47, 2, 'none'),
(115, 'Arabian Sea', 115, '484,220,500,212,532,252,468,316,464,318,462,335,442,334,444,328,451,320,458,318,462,311,460,308,462,297,458,296,458,280,478,258,480,248,483,239', 446, 217, 0, 0, 490, 248, 117, 84, 2, 'none'),
(116, 'Persian Gulf', 116, '502,210,517,196,583,192,561,216,568,212,586,242,534,251', 477, 173, 0, 0, 524, 211, 76, 107, 2, 'none'),
(117, 'Cape of Good Hope', 117, '346,376,389,378,399,374,406,375,424,366,442,337,458,338,458,340,462,352,469,354,450,399,344,399', 346, 339, 0, 0, 395, 379, 58, 120, 2, 'none'),
(118, 'Indian Ocean Mauritius', 118, '482,306,513,275,597,316,596,359,466,369,473,352,478,348,482,336,480,324,484,318', 469, 277, 0, 0, 513, 318, 90, 126, 2, 'none'),
(119, 'Indian Ocean Prince Edward', 119, '464,372,572,364,572,399,454,399', 457, 366, 0, 0, 494, 379, 31, 112, 2, 'none'),
(120, 'Laccadive Sea', 120, '534,253,588,246,596,261,599,263,598,312,516,272', 519, 249, 0, 0, 548, 258, 60, 80, 2, 'none'),
(121, 'Bay of Bengal', 121, '602,262,608,261,609,270,613,275,620,274,620,264,610,236,642,206,652,207,666,232,676,233,680,270,689,288,602,280', 604, 211, 0, 0, 631, 239, 74, 80, 2, 'none'),
(122, 'Indian Ocean Sri Lanka', 122, '602,282,692,292,703,308,667,354,598,359', 601, 286, 0, 0, 621, 304, 71, 100, 2, 'none'),
(123, 'South China Sea', 123, '710,214,730,208,752,214,748,229,752,244,746,254,748,260,758,248,762,255,754,266,744,266,722,248,722,238,708,220', 712, 211, 0, 0, 723, 218, 54, 49, 2, 'none'),
(124, 'Java Sea', 124, '692,246,696,246,708,260,720,250,740,268,731,283,724,283,724,289,716,297,706,272,690,254', 692, 249, 0, 0, 709, 255, 42, 45, 2, 'none'),
(125, 'Indian Ocean Indonesia', 125, '706,308,710,314,744,320,752,323,772,324,764,339,750,344,744,351,742,367,670,354', 675, 317, 0, 0, 699, 326, 47, 94, 2, 'none'),
(126, 'Indian Ocean Center South', 126, '574,364,666,356,696,362,696,399,574,399', 575, 359, 0, 0, 607, 371, 39, 119, 2, 'none'),
(127, 'Sea of Okhotsk', 127, '740,54,746,56,748,52,762,56,769,54,764,48,765,46,772,46,770,38,776,38,773,58,775,68,777,70,793,88,800,88,799,70,790,58,783,56,784,48,794,44,818,66,810,68,814,72,834,69,852,114,800,130,792,116,797,95,788,100,780,99,769,84,750,66,738,70,736,68', 739, 48, 0, 0, 807, 81, 80, 111, 2, 'none'),
(128, 'Northwest Pacific', 128, '836,70,900,40,948,94,945,140,932,136,905,112,880,101,880,107,855,114', 841, 42, 0, 0, 878, 68, 97, 105, 2, 'none'),
(129, 'Pacific Johnston', 129, '856,117,884,109,944,152,942,180,856,180', 858, 112, 0, 0, 874, 143, 67, 85, 2, 'none'),
(130, 'Pacific Shoto', 130, '800,134,854,118,853,181,821,202,818,198,812,197,804,188,774,180,780,151,790,148,794,139,799,139', 776, 122, 0, 0, 805, 143, 79, 75, 2, 'none'),
(131, 'Sea of Japan', 131, '758,84,773,100,775,112,780,126,776,130,778,134,775,136,763,136,750,126,752,114,759,114,762,109,762,92', 753, 94, 0, 0, 757, 106, 41, 26, 2, 'none'),
(132, 'Yellow Sea', 132, '735,128,749,139,756,149,764,138,770,138,765,148,768,152,769,178,748,172,752,156,734,144,734,142,738,136,732,132', 739, 137, 0, 0, 751, 148, 39, 29, 2, 'none'),
(133, 'Philippine Sea', 133, '748,176,801,190,822,244,806,239,800,244,774,242,753,211,754,195', 751, 180, 0, 0, 764, 188, 62, 67, 2, 'none'),
(134, 'Pacific Guam', 134, '855,184,888,184,896,212,884,212,882,216,896,226,861,248,824,240,821,228,829,213,824,204', 823, 186, 0, 0, 846, 194, 61, 71, 2, 'none'),
(135, 'Pacific Nauru', 135, '863,250,898,230,906,240,915,240,941,260,949,288,886,297,874,291,858,258,878,256', 861, 232, 0, 0, 893, 259, 65, 86, 2, 'none'),
(136, 'Bismarck Sea', 136, '776,245,798,247,797,256,810,252,850,260,870,260,854,282,836,274,816,282,784,280,780,274,754,280,756,268,768,265,777,271,780,270,780,254', 757, 248, 0, 0, 790, 254, 33, 109, 2, 'none'),
(137, 'Timor Sea', 137, '725,293,732,302,744,306,765,306,776,298,805,296,808,304,819,304,824,300,830,306,824,313,820,306,818,306,813,324,804,320,807,312,788,310,784,318,772,319,782,306,776,306,768,313,735,312,730,308,716,306', 722, 296, 0, 0, 774, 293, 25, 107, 2, 'none'),
(138, 'Coral Sea', 138, '836,294,844,294,846,288,860,299,888,309,889,321,880,320,880,326,888,334,888,339,838,338,828,316,841,302', 830, 292, 0, 0, 837, 305, 44, 57, 2, 'none'),
(139, 'Great Australian Bight', 139, '698,362,742,370,746,380,743,384,752,390,788,373,793,382,799,382,806,399,698,399', 700, 365, 0, 0, 704, 376, 33, 102, 2, 'none'),
(140, 'Tasman Sea', 140, '840,340,889,342,891,358,854,394,856,399,824,399,824,384,843,364,844,346', 827, 342, 0, 0, 848, 343, 56, 63, 2, 'none'),
(141, 'Pacific Chatham', 141, '912,358,980,354,980,399,870,399,870,395', 872, 357, 0, 0, 917, 367, 40, 106, 2, 'none'),
(142, 'South Sea', 142, '890,300,955,291,955,274,980,274,980,350,902,355,891,339', 893, 275, 0, 0, 917, 307, 79, 85, 2, 'none'),
(143, 'Pacific Kiribati', 143, '890,184,980,184,980,269,951,269,946,258,917,235,915,218,904,208,898,212', 893, 186, 0, 0, 938, 196, 82, 85, 2, 'none');

--
-- Dumping data for table `area_is_adjacent`
--

INSERT INTO `area_is_adjacent` (`id`, `id_area1`, `id_area2`) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 1, 81),
(4, 2, 1),
(5, 2, 3),
(6, 2, 4),
(7, 2, 83),
(8, 3, 1),
(9, 3, 2),
(10, 3, 4),
(11, 3, 7),
(12, 3, 81),
(13, 4, 2),
(14, 4, 83),
(15, 4, 5),
(16, 4, 9),
(17, 4, 8),
(18, 4, 7),
(19, 4, 3),
(20, 5, 4),
(21, 5, 83),
(22, 5, 6),
(23, 5, 10),
(24, 5, 9),
(25, 6, 5),
(26, 6, 83),
(27, 6, 95),
(28, 6, 97),
(29, 6, 10),
(30, 7, 3),
(31, 7, 4),
(32, 7, 8),
(33, 7, 12),
(34, 7, 84),
(35, 7, 82),
(36, 7, 81),
(37, 8, 4),
(38, 8, 9),
(39, 8, 11),
(40, 8, 86),
(41, 8, 12),
(42, 8, 7),
(43, 9, 4),
(44, 9, 5),
(45, 9, 10),
(46, 9, 11),
(47, 9, 8),
(48, 10, 5),
(49, 10, 6),
(50, 10, 97),
(51, 10, 102),
(52, 10, 11),
(53, 10, 9),
(54, 11, 8),
(55, 11, 9),
(56, 11, 10),
(57, 11, 102),
(58, 11, 86),
(59, 12, 7),
(60, 12, 8),
(61, 12, 86),
(62, 12, 13),
(63, 12, 85),
(64, 12, 84),
(65, 13, 12),
(66, 13, 86),
(67, 13, 102),
(68, 13, 87),
(69, 13, 14),
(70, 13, 88),
(71, 13, 85),
(72, 14, 13),
(73, 14, 87),
(74, 14, 16),
(75, 14, 15),
(76, 14, 88),
(77, 15, 14),
(78, 15, 16),
(79, 15, 18),
(80, 15, 19),
(81, 15, 89),
(82, 15, 88),
(83, 16, 14),
(84, 16, 87),
(85, 16, 101),
(86, 16, 104),
(87, 16, 17),
(88, 16, 18),
(89, 16, 15),
(90, 17, 16),
(91, 17, 104),
(92, 17, 105),
(93, 17, 20),
(94, 17, 18),
(95, 18, 15),
(96, 18, 16),
(97, 18, 17),
(98, 18, 20),
(99, 18, 19),
(100, 19, 15),
(101, 19, 18),
(102, 19, 20),
(103, 19, 91),
(104, 19, 89),
(105, 20, 19),
(106, 20, 18),
(107, 20, 17),
(108, 20, 105),
(109, 20, 108),
(110, 20, 91),
(111, 21, 92),
(112, 21, 93),
(113, 21, 96),
(114, 21, 94),
(115, 22, 92),
(116, 22, 110),
(117, 22, 23),
(118, 22, 111),
(119, 22, 93),
(120, 23, 110),
(121, 23, 24),
(122, 23, 28),
(123, 23, 111),
(124, 23, 22),
(125, 24, 23),
(126, 24, 43),
(127, 24, 29),
(128, 24, 28),
(129, 25, 26),
(130, 25, 98),
(131, 25, 99),
(132, 25, 96),
(133, 25, 93),
(134, 26, 25),
(135, 26, 93),
(136, 26, 111),
(137, 26, 27),
(138, 26, 98),
(139, 27, 28),
(140, 27, 112),
(141, 27, 113),
(142, 27, 98),
(143, 27, 26),
(144, 27, 111),
(145, 28, 23),
(146, 28, 24),
(147, 28, 29),
(148, 28, 112),
(149, 28, 27),
(150, 28, 111),
(151, 29, 24),
(152, 29, 43),
(153, 29, 49),
(154, 29, 52),
(155, 29, 30),
(156, 29, 112),
(157, 29, 28),
(158, 30, 29),
(159, 30, 52),
(160, 30, 116),
(161, 30, 31),
(162, 30, 114),
(163, 30, 34),
(164, 30, 113),
(165, 30, 112),
(166, 31, 30),
(167, 31, 116),
(168, 31, 115),
(169, 31, 114),
(170, 32, 98),
(171, 32, 33),
(172, 32, 36),
(173, 32, 99),
(174, 33, 34),
(175, 33, 38),
(176, 33, 36),
(177, 33, 32),
(178, 33, 98),
(179, 33, 113),
(180, 34, 33),
(181, 34, 113),
(182, 34, 30),
(183, 34, 114),
(184, 34, 35),
(185, 34, 39),
(186, 34, 38),
(187, 35, 34),
(188, 35, 114),
(189, 35, 115),
(190, 35, 39),
(191, 36, 32),
(192, 36, 33),
(193, 36, 38),
(194, 36, 37),
(195, 36, 103),
(196, 36, 100),
(197, 36, 99),
(198, 37, 36),
(199, 37, 38),
(200, 37, 107),
(201, 37, 103),
(202, 38, 33),
(203, 38, 34),
(204, 38, 39),
(205, 38, 41),
(206, 38, 40),
(207, 38, 107),
(208, 38, 37),
(209, 38, 36),
(210, 39, 34),
(211, 39, 35),
(212, 39, 115),
(213, 39, 41),
(214, 39, 38),
(215, 40, 38),
(216, 40, 41),
(217, 40, 106),
(218, 40, 107),
(219, 41, 38),
(220, 41, 39),
(221, 41, 115),
(222, 41, 117),
(223, 41, 106),
(224, 41, 40),
(225, 42, 115),
(226, 42, 118),
(227, 42, 117),
(228, 43, 24),
(229, 43, 29),
(230, 43, 49),
(231, 43, 48),
(232, 43, 44),
(233, 44, 43),
(234, 44, 48),
(235, 44, 50),
(236, 44, 47),
(237, 44, 45),
(238, 45, 44),
(239, 45, 47),
(240, 45, 46),
(241, 45, 56),
(242, 46, 45),
(243, 46, 47),
(244, 46, 59),
(245, 46, 58),
(246, 46, 56),
(247, 47, 44),
(248, 47, 45),
(249, 47, 46),
(250, 47, 59),
(251, 47, 51),
(252, 47, 50),
(253, 48, 43),
(254, 48, 44),
(255, 48, 50),
(256, 48, 53),
(257, 48, 49),
(258, 49, 29),
(259, 49, 43),
(260, 49, 48),
(261, 49, 53),
(262, 49, 52),
(263, 50, 44),
(264, 50, 47),
(265, 50, 51),
(266, 50, 54),
(267, 50, 53),
(268, 50, 48),
(269, 51, 47),
(270, 51, 59),
(271, 51, 61),
(272, 51, 62),
(273, 51, 54),
(274, 51, 50),
(275, 52, 29),
(276, 52, 49),
(277, 52, 53),
(278, 52, 116),
(279, 52, 30),
(280, 53, 49),
(281, 53, 48),
(282, 53, 50),
(283, 53, 54),
(284, 53, 55),
(285, 53, 116),
(286, 53, 52),
(287, 54, 50),
(288, 54, 51),
(289, 54, 62),
(290, 54, 63),
(291, 54, 55),
(292, 54, 53),
(293, 55, 53),
(294, 55, 54),
(295, 55, 63),
(296, 55, 121),
(297, 55, 120),
(298, 55, 116),
(299, 56, 45),
(300, 56, 46),
(301, 56, 58),
(302, 56, 127),
(303, 56, 57),
(304, 57, 56),
(305, 57, 127),
(306, 58, 46),
(307, 58, 56),
(308, 58, 127),
(309, 58, 131),
(310, 58, 59),
(311, 59, 46),
(312, 59, 58),
(313, 59, 131),
(314, 59, 132),
(315, 59, 61),
(316, 59, 51),
(317, 59, 47),
(318, 60, 127),
(319, 60, 130),
(320, 60, 132),
(321, 60, 131),
(322, 61, 51),
(323, 61, 59),
(324, 61, 132),
(325, 61, 133),
(326, 61, 62),
(327, 62, 51),
(328, 62, 61),
(329, 62, 133),
(330, 62, 123),
(331, 62, 63),
(332, 62, 54),
(333, 63, 54),
(334, 63, 62),
(335, 63, 123),
(336, 63, 124),
(337, 63, 121),
(338, 63, 55),
(339, 64, 130),
(340, 64, 134),
(341, 64, 133),
(342, 65, 123),
(343, 65, 133),
(344, 65, 136),
(345, 66, 133),
(346, 66, 134),
(347, 66, 135),
(348, 66, 136),
(349, 67, 124),
(350, 67, 123),
(351, 67, 136),
(352, 67, 137),
(353, 68, 136),
(354, 68, 138),
(355, 68, 137),
(356, 69, 136),
(357, 69, 135),
(358, 69, 142),
(359, 69, 138),
(360, 70, 122),
(361, 70, 121),
(362, 70, 124),
(363, 70, 137),
(364, 70, 125),
(365, 71, 125),
(366, 71, 137),
(367, 71, 72),
(368, 71, 74),
(369, 71, 73),
(370, 72, 71),
(371, 72, 137),
(372, 72, 138),
(373, 72, 140),
(374, 72, 74),
(375, 73, 125),
(376, 73, 71),
(377, 73, 74),
(378, 73, 139),
(379, 74, 73),
(380, 74, 71),
(381, 74, 72),
(382, 74, 140),
(383, 74, 139),
(384, 75, 140),
(385, 75, 142),
(386, 75, 141),
(387, 76, 127),
(388, 76, 128),
(389, 76, 81),
(390, 77, 128),
(391, 77, 129),
(392, 78, 128),
(393, 78, 82),
(394, 78, 84),
(395, 78, 129),
(396, 79, 134),
(397, 79, 143),
(398, 79, 135),
(399, 80, 135),
(400, 80, 143),
(401, 80, 142),
(402, 81, 1),
(403, 81, 3),
(404, 81, 7),
(405, 81, 82),
(406, 81, 128),
(407, 81, 76),
(408, 82, 81),
(409, 82, 7),
(410, 82, 84),
(411, 82, 78),
(412, 82, 128),
(413, 83, 2),
(414, 83, 4),
(415, 83, 5),
(416, 83, 6),
(417, 83, 95),
(418, 84, 7),
(419, 84, 12),
(420, 84, 85),
(421, 84, 143),
(422, 84, 78),
(423, 84, 82),
(424, 85, 12),
(425, 85, 13),
(426, 85, 88),
(427, 85, 143),
(428, 85, 84),
(429, 86, 8),
(430, 86, 11),
(431, 86, 102),
(433, 86, 13),
(434, 86, 12),
(435, 87, 13),
(436, 87, 102),
(437, 87, 101),
(438, 87, 16),
(439, 87, 14),
(440, 87, 88),
(441, 88, 85),
(442, 88, 13),
(443, 88, 87),
(444, 88, 14),
(445, 88, 15),
(446, 88, 89),
(447, 88, 90),
(448, 88, 142),
(449, 88, 143),
(450, 89, 15),
(451, 89, 19),
(452, 89, 91),
(453, 89, 90),
(454, 89, 88),
(455, 90, 88),
(456, 90, 89),
(457, 90, 91),
(458, 90, 141),
(459, 90, 142),
(460, 91, 89),
(461, 91, 19),
(462, 91, 20),
(463, 91, 108),
(464, 91, 90),
(465, 92, 110),
(466, 92, 22),
(467, 92, 93),
(468, 92, 21),
(469, 92, 94),
(470, 93, 92),
(471, 93, 22),
(472, 93, 111),
(473, 93, 26),
(474, 93, 25),
(475, 93, 96),
(476, 93, 21),
(477, 94, 92),
(478, 94, 21),
(479, 94, 96),
(480, 94, 95),
(481, 95, 94),
(482, 95, 96),
(483, 95, 97),
(484, 95, 6),
(485, 95, 83),
(486, 96, 94),
(487, 96, 21),
(488, 96, 93),
(489, 96, 25),
(490, 96, 99),
(491, 96, 97),
(492, 96, 95),
(493, 97, 95),
(494, 97, 96),
(495, 97, 99),
(496, 97, 102),
(497, 97, 10),
(498, 97, 6),
(499, 98, 25),
(500, 98, 26),
(501, 98, 27),
(502, 98, 113),
(503, 98, 33),
(504, 98, 32),
(505, 98, 99),
(506, 99, 96),
(507, 99, 25),
(508, 99, 98),
(509, 99, 32),
(510, 99, 36),
(511, 99, 100),
(512, 99, 101),
(513, 99, 102),
(514, 99, 97),
(515, 100, 99),
(516, 100, 36),
(517, 100, 103),
(518, 100, 104),
(519, 100, 101),
(520, 101, 102),
(521, 101, 99),
(522, 101, 100),
(523, 101, 104),
(524, 101, 16),
(525, 101, 87),
(526, 102, 97),
(527, 102, 99),
(528, 102, 101),
(529, 102, 87),
(530, 102, 13),
(531, 102, 86),
(532, 102, 11),
(533, 102, 10),
(534, 103, 36),
(535, 103, 37),
(536, 103, 107),
(537, 103, 104),
(538, 103, 100),
(539, 104, 100),
(540, 104, 103),
(541, 104, 107),
(542, 104, 105),
(543, 104, 17),
(544, 104, 16),
(545, 104, 101),
(546, 105, 104),
(547, 105, 107),
(548, 105, 106),
(549, 105, 109),
(550, 105, 108),
(551, 105, 20),
(552, 105, 17),
(553, 106, 107),
(554, 106, 40),
(555, 106, 41),
(556, 106, 117),
(557, 106, 109),
(558, 106, 105),
(559, 107, 37),
(560, 107, 38),
(561, 107, 40),
(562, 107, 106),
(563, 107, 105),
(564, 107, 104),
(565, 107, 103),
(566, 108, 105),
(567, 108, 109),
(568, 108, 91),
(569, 108, 20),
(570, 109, 108),
(571, 109, 105),
(572, 109, 106),
(573, 109, 117),
(574, 110, 92),
(575, 110, 22),
(576, 110, 23),
(577, 111, 93),
(578, 111, 22),
(579, 111, 23),
(580, 111, 28),
(581, 111, 27),
(582, 111, 26),
(583, 112, 27),
(584, 112, 28),
(585, 112, 29),
(586, 112, 30),
(587, 112, 113),
(588, 113, 112),
(589, 113, 30),
(590, 113, 114),
(591, 113, 34),
(592, 113, 33),
(593, 113, 98),
(594, 113, 27),
(595, 114, 113),
(596, 114, 30),
(597, 114, 31),
(598, 114, 115),
(599, 114, 35),
(600, 114, 34),
(601, 115, 114),
(602, 115, 31),
(603, 115, 116),
(604, 115, 120),
(605, 115, 118),
(606, 115, 42),
(607, 115, 117),
(608, 115, 41),
(609, 115, 39),
(610, 115, 35),
(611, 116, 31),
(612, 116, 30),
(613, 116, 52),
(614, 116, 53),
(615, 116, 55),
(616, 116, 120),
(617, 116, 115),
(618, 117, 109),
(619, 117, 106),
(620, 117, 41),
(621, 117, 115),
(622, 117, 42),
(623, 117, 118),
(624, 117, 119),
(625, 118, 115),
(626, 118, 120),
(627, 118, 122),
(628, 118, 126),
(629, 118, 119),
(630, 118, 117),
(631, 118, 42),
(632, 119, 117),
(633, 119, 118),
(634, 119, 126),
(635, 120, 115),
(636, 120, 116),
(637, 120, 55),
(638, 120, 121),
(639, 120, 122),
(640, 120, 118),
(641, 121, 120),
(642, 121, 55),
(643, 121, 63),
(644, 121, 124),
(645, 121, 70),
(646, 121, 122),
(647, 122, 120),
(648, 122, 121),
(649, 122, 70),
(650, 122, 125),
(651, 122, 126),
(652, 122, 118),
(653, 123, 63),
(654, 123, 62),
(655, 123, 133),
(656, 123, 65),
(657, 123, 136),
(658, 123, 67),
(659, 123, 124),
(660, 124, 63),
(661, 124, 123),
(662, 124, 67),
(663, 124, 137),
(664, 124, 70),
(665, 124, 121),
(666, 125, 122),
(667, 125, 70),
(668, 125, 137),
(669, 125, 71),
(670, 125, 73),
(671, 125, 139),
(672, 125, 126),
(673, 126, 119),
(674, 126, 118),
(675, 126, 122),
(676, 126, 125),
(677, 126, 139),
(678, 127, 56),
(679, 127, 57),
(680, 127, 76),
(681, 127, 128),
(682, 127, 130),
(683, 127, 60),
(684, 127, 131),
(685, 127, 58),
(686, 128, 127),
(687, 128, 76),
(688, 128, 81),
(689, 128, 82),
(690, 128, 78),
(691, 128, 129),
(692, 128, 77),
(693, 129, 130),
(694, 129, 128),
(695, 129, 77),
(696, 129, 78),
(697, 129, 84),
(698, 129, 143),
(699, 129, 134),
(700, 130, 132),
(701, 131, 60),
(706, 136, 133),
(707, 131, 59),
(708, 131, 58),
(709, 131, 127),
(711, 131, 132),
(712, 132, 61),
(713, 132, 59),
(714, 132, 131),
(715, 132, 60),
(716, 132, 130),
(717, 132, 133),
(718, 133, 61),
(719, 133, 132),
(720, 133, 130),
(721, 133, 64),
(722, 133, 134),
(723, 133, 66),
(724, 133, 136),
(725, 133, 65),
(726, 133, 123),
(727, 133, 62),
(728, 134, 133),
(729, 134, 64),
(730, 134, 130),
(731, 134, 129),
(732, 134, 143),
(733, 134, 79),
(734, 134, 135),
(735, 134, 66),
(736, 135, 134),
(737, 135, 79),
(738, 135, 143),
(739, 135, 80),
(740, 135, 142),
(741, 135, 69),
(742, 135, 136),
(743, 135, 66),
(744, 136, 66),
(745, 136, 135),
(746, 136, 69),
(747, 136, 138),
(748, 136, 68),
(749, 136, 137),
(750, 136, 67),
(751, 136, 123),
(752, 136, 65),
(754, 137, 67),
(755, 137, 136),
(756, 137, 68),
(757, 137, 138),
(758, 137, 72),
(759, 137, 71),
(760, 137, 125),
(761, 137, 70),
(762, 137, 124),
(763, 138, 136),
(764, 138, 69),
(765, 138, 142),
(766, 138, 140),
(767, 138, 72),
(768, 138, 137),
(769, 138, 68),
(770, 139, 126),
(771, 139, 125),
(772, 139, 73),
(773, 139, 74),
(774, 139, 140),
(775, 140, 139),
(776, 140, 74),
(777, 140, 72),
(778, 140, 138),
(779, 140, 142),
(780, 140, 75),
(781, 140, 141),
(782, 141, 140),
(783, 141, 75),
(784, 141, 142),
(785, 141, 90),
(786, 142, 141),
(787, 142, 75),
(788, 142, 140),
(789, 142, 138),
(790, 142, 69),
(791, 142, 135),
(792, 142, 80),
(793, 142, 143),
(794, 142, 88),
(795, 142, 90),
(796, 143, 142),
(797, 143, 80),
(798, 143, 135),
(799, 143, 79),
(800, 143, 134),
(801, 143, 129),
(802, 143, 84),
(803, 143, 85),
(804, 143, 88),
(805, 84, 129),
(806, 130, 60),
(807, 130, 64),
(808, 130, 127),
(809, 130, 129),
(810, 130, 133),
(811, 130, 134),
(812, 86, 87),
(813, 87, 86),
(814, 93, 94),
(815, 94, 93);

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `key`) VALUES
(1, 'red', 'red'),
(2, 'blue', 'blue'),
(3, 'green', 'green'),
(4, 'orange', 'orange'),
(5, 'purple', 'purple'),
(6, 'yellow', 'yellow');

--
-- Dumping data for table `option_types`
--

INSERT INTO `option_types` (`id`, `units`, `countries`) VALUES
(1, 4, 1),
(2, 3, 1),
(3, 2, 1),
(4, 2, 2),
(5, 2, 3),
(6, 2, 4);

--
-- Dumping data for table `phases`
--

INSERT INTO `phases` (`id`, `name`, `key`) VALUES
(1, 'Move Troops', 'landmove'),
(2, 'Move Fleets', 'seamove'),
(3, 'Trade Routes', 'traderoutes'),
(4, 'Arrange Troops', 'troopsmove'),
(5, 'Reinforcements', 'production'),
(21, 'Game Start', 'startgame'),
(22, 'Select Start-Areas', 'selectstart'),
(23, 'Place Fleets', 'setships');

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `name`, `key`) VALUES
(1, 'Öl', 'oil'),
(2, 'Transport', 'transport'),
(3, 'Industrie', 'industry'),
(4, 'Mineralien', 'minerals'),
(5, 'Population', 'population');

--
-- Dumping data for table `areas_get_resources`
--

INSERT INTO `areas_get_resources` (`id`, `id_resource`, `res_power`, `economy`, `count`) VALUES
(1, 1, 1, 'poor', 1),
(2, 1, 1, 'weak', 6),
(3, 1, 2, 'weak', 6),
(4, 1, 3, 'normal', 5),
(5, 1, 4, 'strong', 3),
(6, 2, 1, 'poor', 1),
(7, 2, 2, 'poor', 4),
(8, 2, 2, 'weak', 3),
(9, 2, 3, 'weak', 1),
(10, 2, 3, 'normal', 4),
(11, 2, 4, 'strong', 2),
(12, 3, 1, 'poor', 1),
(13, 3, 2, 'poor', 4),
(14, 3, 2, 'weak', 3),
(15, 3, 3, 'weak', 2),
(16, 3, 3, 'normal', 3),
(17, 3, 4, 'strong', 2),
(18, 4, 1, 'poor', 1),
(19, 4, 2, 'poor', 4),
(20, 4, 2, 'weak', 3),
(21, 4, 3, 'weak', 2),
(22, 4, 3, 'normal', 3),
(23, 4, 4, 'strong', 2),
(24, 5, 1, 'poor', 14);

--
-- Dumping data for table `start_sets`
--

INSERT INTO `start_sets` (`id`, `players`) VALUES
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 6),
(8, 6),
(9, 6),
(10, 6),
(11, 6),
(12, 6);

--
-- Dumping data for table `start_set_has_areas`
--

INSERT INTO `start_set_has_areas` (`id`, `id_area`, `id_optiontype`, `id_set`, `option_group`) VALUES
(1, 24, 1, 1, 1),
(2, 3, 2, 1, 2),
(3, 79, 3, 1, 3),
(4, 36, 4, 1, 4),
(5, 37, 4, 1, 4),
(6, 38, 4, 1, 4),
(7, 61, 4, 1, 5),
(8, 62, 4, 1, 5),
(9, 63, 4, 1, 5),
(10, 28, 1, 2, 1),
(11, 6, 2, 2, 2),
(12, 68, 4, 2, 3),
(13, 69, 4, 2, 3),
(14, 66, 4, 2, 3),
(15, 17, 4, 2, 4),
(16, 19, 4, 2, 4),
(17, 20, 4, 2, 4),
(18, 54, 3, 2, 5),
(19, 55, 3, 2, 5),
(20, 10, 1, 3, 1),
(21, 80, 3, 3, 3),
(22, 23, 2, 3, 2),
(23, 27, 2, 3, 2),
(24, 40, 4, 3, 4),
(25, 41, 4, 3, 4),
(26, 42, 4, 3, 4),
(27, 44, 4, 3, 5),
(28, 47, 4, 3, 5),
(29, 50, 4, 3, 5),
(30, 25, 1, 4, 1),
(31, 22, 1, 4, 1),
(32, 49, 2, 4, 2),
(33, 2, 3, 4, 3),
(34, 15, 4, 4, 4),
(35, 16, 4, 4, 4),
(36, 14, 4, 4, 4),
(37, 78, 4, 4, 5),
(38, 76, 4, 4, 5),
(39, 77, 4, 4, 5),
(40, 26, 1, 5, 1),
(41, 43, 2, 5, 2),
(42, 48, 2, 5, 2),
(43, 12, 4, 5, 3),
(44, 13, 4, 5, 3),
(45, 65, 5, 5, 4),
(46, 64, 5, 5, 4),
(47, 67, 5, 5, 4),
(48, 70, 5, 5, 4),
(49, 9, 1, 6, 1),
(50, 11, 1, 6, 1),
(51, 75, 2, 6, 2),
(52, 57, 4, 6, 3),
(53, 58, 4, 6, 3),
(54, 56, 4, 6, 3),
(55, 35, 5, 6, 4),
(56, 30, 5, 6, 4),
(57, 34, 5, 6, 4),
(58, 31, 5, 6, 4),
(59, 33, 5, 6, 4),
(60, 24, 1, 7, 1),
(61, 3, 2, 7, 2),
(62, 79, 3, 7, 3),
(63, 36, 4, 7, 4),
(64, 37, 4, 7, 4),
(65, 38, 4, 7, 4),
(66, 61, 4, 7, 5),
(67, 62, 4, 7, 5),
(68, 63, 4, 7, 5),
(69, 28, 1, 8, 1),
(70, 6, 2, 8, 2),
(71, 68, 4, 8, 3),
(72, 69, 4, 8, 3),
(73, 66, 4, 8, 3),
(74, 17, 4, 8, 4),
(75, 19, 4, 8, 4),
(76, 20, 4, 8, 4),
(77, 54, 3, 8, 5),
(78, 55, 3, 8, 5),
(79, 10, 1, 9, 1),
(80, 80, 3, 9, 3),
(81, 23, 2, 9, 2),
(82, 27, 2, 9, 2),
(83, 40, 4, 9, 4),
(84, 41, 4, 9, 4),
(85, 42, 4, 9, 4),
(86, 44, 4, 9, 5),
(87, 47, 4, 9, 5),
(88, 50, 4, 9, 5),
(89, 25, 1, 10, 1),
(90, 22, 1, 10, 1),
(91, 49, 2, 10, 2),
(92, 2, 3, 10, 3),
(93, 15, 4, 10, 4),
(94, 16, 4, 10, 4),
(95, 14, 4, 10, 4),
(96, 78, 4, 10, 5),
(97, 76, 4, 10, 5),
(98, 77, 4, 10, 5),
(99, 26, 1, 11, 1),
(100, 43, 2, 11, 2),
(101, 48, 2, 11, 2),
(102, 12, 4, 11, 3),
(103, 13, 4, 11, 3),
(104, 65, 5, 11, 4),
(105, 64, 5, 11, 4),
(106, 67, 5, 11, 4),
(107, 70, 5, 11, 4),
(108, 9, 1, 12, 1),
(109, 11, 1, 12, 1),
(110, 75, 2, 12, 2),
(111, 57, 4, 12, 3),
(112, 58, 4, 12, 3),
(113, 56, 4, 12, 3),
(114, 35, 5, 12, 4),
(115, 30, 5, 12, 4),
(116, 34, 5, 12, 4),
(117, 31, 5, 12, 4),
(118, 33, 5, 12, 4);

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `abbreviation`, `price`, `tanksize`, `hitpoints`, `speed`, `id_type`) VALUES
(1, 'Infanterie', 'Inf', 2, NULL, 1, 1, 1),
(2, 'Artillerie', 'Art', 3, NULL, 1, 1, 1),
(3, 'Panzer', 'Pan', 4, NULL, 1, 2, 1),
(4, 'Flieger', 'Flug', 5, NULL, 1, 2, 3),
(5, 'U-Boot(e)', NULL, 3, 4, 2, 1, 2),
(6, 'Zerstörer', NULL, 3, 3, 2, 1, 2),
(7, 'Schlachtschiff(e)', NULL, 5, 7, 4, 1, 2),
(8, 'Flugzeugträger', NULL, 7, 7, 4, 1, 2);

--
-- Dumping data for table `start_ships`
--

INSERT INTO `start_ships` (`id`, `id_unit`, `numberof`, `players`) VALUES
(1, 7, 2, 2),
(2, 6, 4, 2),
(3, 8, 2, 2),
(4, 5, 4, 2),
(5, 7, 2, 6),
(6, 6, 4, 6),
(7, 8, 2, 6),
(8, 5, 4, 6);

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `name`, `players`, `id_creator`, `password`, `status`, `id_phase`, `round`, `processing`) VALUES
(0, 'DUMMY', 0, -1, 'dummy', 'done', 21, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
