<?php
namespace PowerMocker;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PowerMocker\Filter\Autoload;

/**
 * 转换代码
 * Class Transform
 * @package PowerMocker
 */
class Transform
{
    /**
     * code
     * @var string
     */
    private $code = '';

    /**
     * ast
     * @var
     */
    private $ast;

    /**
     * run
     * @param string $code
     */
    public function run($code)
    {
        $this->code = $code;
        if (empty($code)) {
            return '';
        }
        $this->ast = $this->getAst();
        $this->change();
        return $this->getChangedCode();
    }

    /**
     * get ast
     * @return Node\Stmt[]|null
     */
    public function getAst()
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        return $parser->parse($this->code);
    }

    /**
     * 改变代码
     * @return Node[]
     */
    public function change()
    {
        $traverser = new NodeTraverser();
//        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor(new class extends NodeVisitorAbstract {
            public function leaveNode(Node $node)
            {
                if ($node instanceof Node\Stmt\Expression
                    && $node->expr instanceof Node\Expr\Include_
                    && $node->expr->expr instanceof Node\Expr\Variable
                ) { //如果是引入文件，则使用拦截的方式
                    //TODO:目前只支持引入后面是一个变量的情况，如"require_once $file";
                    $filterName = Autoload::NAME;
                    $concat = new Node\Expr\BinaryOp\Concat(new Node\Scalar\String_("php://filter/read={$filterName}/resource="), new Node\Expr\Variable($node->expr->expr->name));
                    $node->expr->expr = $concat;
                    return $node;
                }

                if ($node instanceof Node\Stmt\ClassMethod) { //注入方法
                    $e = new Node\Stmt\Label("return \PowerMocker\Proxy::instance()->callByMock(__METHOD__, func_get_args()); #此方法是由PowerMock拦截添加");
                    $t = new Node\Stmt\TryCatch([$e], [new Node\Stmt\Catch_([new Node\Name\FullyQualified('PowerMocker\PowerMockException')], new Node\Expr\Variable('e'))]);
                    array_unshift($node->stmts, $t);
                }

                //替换部分系统函数，使其支持mock
                if ($node instanceof Node\Name) {
                    $funcName = $node->parts[0];
                    if (in_array($funcName, ['time', 'strtotime'])) {
                        $ucFuncName = ucfirst($funcName);
                        $node->parts[0] = "\PowerMocker\Proxy::instance()->call{$ucFuncName}";
                        return $node;
                    }
                }
            }
        });
        return $traverser->traverse($this->ast);
    }

    /**
     * 获取转换后的代码
     * @return string
     */
    private function getChangedCode()
    {
        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrintFile($this->ast);
    }

    /**
     * 获取转换后的代码
     * @return string
     */
    public function __toString()
    {
        return $this->getChangedCode();
    }
}
