<?php

    interface MyInterface {
        const TEST_CONSTANT = true;
    }

    class ParentClass implements MyInterface {
    }

    class ChildClass extends ParentClass {
    }

?>