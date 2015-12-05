<?php namespace Mayconbordin\Generator\Parsers;

class NameParser
{
    const ACTIONS = [
        'create' => 'create', 'make'    => 'create',
        'delete' => 'delete', 'remove'  => 'delete',
        'drop'   => 'drop'  , 'destroy' => 'drop'  ,
        'add'    => 'add'   , 'append'  => 'add'   , 'update' => 'add', 'insert' => 'add',
    ];

    /**
     * Parse the migration name into something we can use.
     *
     * @param  string $name
     * @return array
     */
    public function parse($name)
    {
        $segments = array_reverse(explode('_', $name));

        if ($segments[0] == 'table') {
            array_shift($segments);
        }

        return [
            'action' => $this->getAction($segments),
            'table'  => $this->getTableName($segments)
        ];
    }

    /**
     * Calculate the table name.
     *
     * @param  array $segments
     * @return array
     */
    private function getTableName($segments)
    {
        $tableName = [];

        foreach ($segments as $segment) {
            if ($this->isConnectingWord($segment)) {
                break;
            }

            $tableName[] = $segment;
        }

        return implode('_', array_reverse($tableName));
    }

    /**
     * Determine the user's desired action for the migration.
     *
     * @param  array $segments
     * @return mixed
     */
    private function getAction(&$segments)
    {
        if ($this->normalizeActionName(array_last($segments)) != null) {
            return $this->normalizeActionName(array_pop($segments));
        }

        return null;
    }

    /**
     * Normalize the user's chosen action to name to
     * something that we recognize.
     *
     * @param  string $action
     * @return string
     */
    private function normalizeActionName($action)
    {
        return array_get(self::ACTIONS, $action, null);
    }

    /**
     * Determine if the current segment is a connecting word.
     *
     * @param  string $segment
     * @return bool
     */
    private function isConnectingWord($segment)
    {
        $connectors = ['to', 'from', 'and', 'with', 'for', 'in', 'of', 'on'];
        return in_array($segment, $connectors);
    }
}
