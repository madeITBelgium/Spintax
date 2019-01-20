<?php

namespace MadeITBelgium\Spintax;

use Countable;

/**
 * @version    1.0.0
 *
 * @copyright  Copyright (c) 2019 Made I.T. (http://www.madeit.be)
 * @author     Tjebbe Lievens <tjebbe.lievens@madeit.be>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-3.txt    LGPL
 */
class Content implements Countable
{
    protected $content;

    protected $children = [];

    protected $next;

    public function __construct($content = '')
    {
        $this->setContent($content);
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function addChild(self $child)
    {
        $this->children[] = $child;

        return $this;
    }

    public function setNext(self $next)
    {
        $this->next = $next;

        return $this;
    }

    public function generate(array &$path = [], &$index = 0)
    {
        $content = $this->content;
        // pick child value
        if (!empty($this->children)) {
            // pick random child
            if (!isset($path[$index])) {
                $path[$index] = \rand(0, count($this->children) - 1);
            }
            $option = $path[$index];
            $index++;
            $content .= $this->children[$option]->generate($path, $index);
        }
        // continue further
        if (isset($this->next)) {
            $content .= $this->next->generate($path, $index);
        }

        return $content;
    }

    public function dump()
    {
        $content = $this->content;
        // dump all possible children paths
        if (!empty($this->children)) {
            $options = [];
            foreach ($this->children as $child) {
                $options[] = $child->dump();
            }
            $content .= '{'.implode('|', $options).'}';
        }
        // continue further
        if (isset($this->next)) {
            $content .= $this->next->dump();
        }

        return $content;
    }

    public function getPaths()
    {
        if (empty($this->children)) {
            return [[]];
        } else {
            // list of paths, from which each is list of steps
            $paths = [];
            foreach ($this->children as $key => $child) {
                foreach ($child->getPaths() as $path) {
                    \array_unshift($path, $key);
                    $paths[] = $path;
                }
            }
            if (isset($this->next)) {
                $prefixes = $paths;
                $paths = [];
                // merge with all further paths
                foreach ($this->next->getPaths() as $path) {
                    foreach ($prefixes as $prefix) {
                        $paths[] = array_merge($prefix, $path);
                    }
                }
            }

            return $paths;
        }
    }

    public function count()
    {
        // self-caontained at least
        $count = 1;
        // all sub-trees
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $count += \count($child);
            }
            $count--;
        }
        // also use further content
        if (isset($this->next)) {
            $count *= \count($this->next);
        }

        return $count;
    }

    public function getAll()
    {
        $path = [];
        $result = [];

        foreach ($this->generateArray() as $content) {
            $result[] = $content;
        }

        return $result;
    }

    private function generateArray()
    {
        $content = $this->content;
        $results = [];

        // pick child value
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $childContents = $child->generateArray();
                foreach ($childContents as $childContent) {
                    $results[] = $content.$childContent;
                }
            }
        } else {
            $results[] = $content;
        }

        // continue further
        if (isset($this->next)) {
            $endResult = [];
            $contents = $this->next->generateArray();
            for ($i = 0; $i < count($results); $i++) {
                for ($j = 0; $j < count($contents); $j++) {
                    $endResult[] = $results[$i].$contents[$j];
                }
            }
            $results = $endResult;
        }

        return $results;
    }

    public function __toString()
    {
        return $this->dump();
    }
}
