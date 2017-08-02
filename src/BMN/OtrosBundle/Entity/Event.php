<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 05/05/2015
 * Time: 9:41
 */

namespace BMN\OtrosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BMN\OtrosBundle\Entity\Event
 *
 * @ORM\Entity()
 * @ORM\Table(name="event")
 */
class Event
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="boolean")
     */
    private $allDay; // a boolean

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $start; // a DateTime

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * @param string $allDay
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    } // a DateTime, or null


    // Converts this Event object back to a plain data array, to be used for generating JSON
    public function toArray()
    {

        $array['id'] = $this->id;
        $array['title'] = $this->title;
        $array['description'] = $this->description;
        $array['color'] = $this->color;
        $array['url'] = $this->url;
        $array['description'] = $this->description;

        // Figure out the date format. This essentially encodes allDay into the date string.
        if ($this->allDay) {
            $format = /*'d/m/Y'*/
                'Y-m-d'; // output like "2013-12-29"
        } else {
            $format = 'c'; // full ISO8601 output, like "2013-12-29T09:00:00+08:00"
        }

        // Serialize dates into strings
        $array['start'] = $this->start->format($format);
        if (isset($this->end)) {
            $array['end'] = $this->end->format($format);
        }

        return $array;
    }

    // Returns whether the date range of our event intersects with the given all-day range.
    // $rangeStart and $rangeEnd are assumed to be dates in UTC with 00:00:00 time.
    public function isWithinDayRange($rangeStart, $rangeEnd)
    {

        // Normalize our event's dates for comparison with the all-day range.
        $eventStart = $this->stripTime($this->start);
//        $eventStart->setTimezone($rangeStart->getTimeZone());
        $eventEnd = isset($this->end) ? $this->stripTime($this->end) : null;
//        $eventEnd->setTimezone($rangeEnd->getTimeZone());

        if (!$eventEnd) {
            // No end time? Only check if the start is within range.
            return $eventStart >= $rangeStart
            && $eventStart <= $rangeEnd;
        } else {
            // Check if the two ranges intersect.
            return $eventStart >= $rangeStart
            && $eventEnd <= $rangeEnd;
        }
    }



// Takes the year/month/date values of the given DateTime and converts them to a new DateTime,
// but in UTC.
    function stripTime($datetime)
    {
        return new \DateTime(
            $datetime->format(/*'d/m/Y'*/
                'Y-m-d H:i:s'
            )
        );
    }
}