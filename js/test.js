class Temp{
    prop = 'd';
    propObj = new B();
}

class A {
    prop = 'q';
    propObj = new Temp();

    cloneProp(){
        this.propObjClone = JSON.parse(JSON.stringify(this.propObj.propObj));
    }
}

class B {
    prop = 'r';
}

var a = new A();