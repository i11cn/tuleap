<?php
/**
 * Copyright (c) Enalean, 2012 - 2018. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * I'm responsible of
 * - displaying a Burndown chart
 * - prepare data for display
 */
class Tracker_Chart_Burndown
{
    /**
     * @var GraphOnTrackersV5_Burndown_Data
     */
    private $burndown_data;

    private $start_date;
    private $duration = 10;
    protected $title = '';
    protected $description = '';
    protected $width = 640;
    protected $height = 480;

    private $graph_data_ideal_burndown   = array();
    private $graph_data_human_dates      = array();
    private $graph_data_remaining_effort = array();

    public function __construct(GraphOnTrackersV5_Burndown_Data $burndown_data)
    {
        $this->burndown_data = $burndown_data;
        $this->start_date    = $burndown_data->getTimePeriod()->getStartDate();
    }

    public function setStartDate($start_date)
    {
        $this->start_date = round($start_date);
    }

    public function setStartDateInDays($start_date)
    {
        $this->start_date = $start_date;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getGraphDataHumanDates()
    {
        return $this->graph_data_human_dates;
    }

    public function getGraphDataRemainingEffort()
    {
        return $this->graph_data_remaining_effort;
    }

    public function getGraphDataIdealBurndown()
    {
        return $this->graph_data_ideal_burndown;
    }

    public function getComputedData()
    {
        $remaining_effort = $this->burndown_data->getRemainingEffort();

        $date           = new DateTime();
        $date->setTimestamp($this->burndown_data->getTimePeriod()->getStartDate());

        $data                  = array();
        $last_remaining_effort = null;

        foreach($remaining_effort as $day => $effort) {
            if ($day < $date->format("Ymd")) {
                if ($last_remaining_effort !== null) {
                    $last_remaining_effort = array_merge($effort, $last_remaining_effort);
                } else {
                    $last_remaining_effort = $effort;
                }
            }
        }

        foreach ($this->burndown_data->getTimePeriod()->getDayOffsets() as $day) {
            if ($date->getTimestamp() <= time()) {
                if (isset($remaining_effort[$date->format('Ymd')])) {
                    foreach ($remaining_effort[$date->format('Ymd')] as $artifact => $value) {
                        if (isset($last_remaining_effort[$artifact])) {
                            unset($last_remaining_effort[$artifact]);
                        }

                        $last_remaining_effort[$artifact] = $value;
                    }

                    $data[$date->format('D d')] = array(array_sum($last_remaining_effort));
                } else {
                    if ($last_remaining_effort) {
                        $data[$date->format('D d')] = array(array_sum($last_remaining_effort));
                        $last_remaining_effort[$date->format('Ymd')] = array(array_sum($last_remaining_effort));
                    } else {
                        $data[$date->format('D d')] = null;
                    }
                }
            } else {
                $data[$date->format('D d')] = null;
            }

            $date->add(new DateInterval('P1D'));
        }

        return $data;
    }

    protected function getFirstEffortNotNull( array $remaining_effort)
    {
        foreach($remaining_effort as $effort) {
            if (is_array($effort) && ($sum_of_effort = floatval(array_sum($effort))) > 0) {
                return $sum_of_effort;
            }
        }
        return 0;
    }

    /**
     * Ideal burndown line:  a * x + b
     * where b is the sum of effort for the first day
     *       x is the number of days (starting from 0 to duration
     *       a is the slope of the line equals -b/duration (burn down goes down)
     *
     * Final formula: slope * day_num + first_day_effort
     *
     * Build data for initial estimation
     */
    public function prepareDataForGraph(array $remaining_effort)
    {
        $start_effort = $this->getFirstEffortNotNull($remaining_effort);
        $slope        = -$start_effort / $this->duration;

        $day_num = 0;
        foreach ($remaining_effort as $day => $effort) {
            $this->graph_data_ideal_burndown[]   = floatval($slope * $day_num + $start_effort);
            $this->graph_data_human_dates[]      = $day;
            if (is_array($effort)) {
                $this->graph_data_remaining_effort[] = array_sum($effort);
            } else {
                $this->graph_data_remaining_effort[] = null;
            }

            $day_num++;
        }
    }

    /**
     * @return Chart
     */
    public function buildGraph()
    {
        $this->prepareDataForGraph($this->getComputedData());

        $graph = new Chart($this->width, $this->height);
        $graph->SetScale("datlin");

        $graph->title->Set($this->title);
        $graph->subtitle->Set($this->description);

        $colors = $graph->getThemedColors();

        $graph->xaxis->SetTickLabels($this->graph_data_human_dates);

        $remaining_effort = new LinePlot($this->graph_data_remaining_effort);
        $graph->Add($remaining_effort);
        $remaining_effort->SetColor($colors[1] . ':0.7');
        $remaining_effort->SetWeight(2);
        $remaining_effort->SetLegend('Remaining effort');
        $remaining_effort->mark->SetType(MARK_FILLEDCIRCLE);
        $remaining_effort->mark->SetColor($colors[1] . ':0.7');
        $remaining_effort->mark->SetFillColor($colors[1]);
        $remaining_effort->mark->SetSize(3);

        $ideal_burndown = new LinePlot($this->graph_data_ideal_burndown);
        $graph->Add($ideal_burndown);
        $ideal_burndown->SetColor($colors[0] . ':1.25');
        $ideal_burndown->SetLegend('Ideal Burndown');

        $graph->legend->SetPos(0.05, 0.5, 'right', 'center');
        $graph->legend->SetColumns(1);

        return $graph;
    }

    public function display()
    {
        $this->buildGraph()->stroke();
    }

    protected function setLastValueWhenRemainingEffortIsBeforeStartDate(DateTime $date, array $remaining_effort)
    {
        if (key($remaining_effort) < $date->format("Ymd") && isset($remaining_effort[key($remaining_effort)])) {
            return $remaining_effort[key($remaining_effort)];
        }

        return null;
    }
}
