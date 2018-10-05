var getCodeSnippets = function() {
    return [
        {
            name: "pwfind",
            content: "d(\\$pages->find(\"template=\${1:basic-page}\")->each(\"\${2:title}\"));"
        },
        {
            name: "pwforeach",
            content: "foreach(\\$pages->find(\"template=\${1:basic-page}\") as \\$p) {\n\td(\\$p);\n}"
        }
    ]
};