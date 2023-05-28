<?php

namespace App\Service;

use App\Entity\Program;

class ProgramDuration
{
    public function calculate(Program $program): string
    {
        $minutes = 0;
        $seasons = $program->getSeasons();
        foreach ($seasons as $season) {
            $episodes = $season->getEpisodes();
            foreach ($episodes as $episode) {
                $minutes = $minutes + $episode->getDuration();
            }
        }
        $d = floor ($minutes / 1440);
        $h = floor (($minutes - $d * 1440) / 60);
        $m = $minutes - ($d * 1440) - ($h * 60);

        return "Il faut {$minutes} minutes pour visionner l'ensemble de la série. Soit {$d} jour(s) {$h} heure(s) et {$m} minute(s).";
    }
}