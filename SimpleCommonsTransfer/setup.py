import distutils.core

with open('requirements.txt') as f:
    reqs = f.read().splitlines()

distutils.core.setup(
    name='SimpleCommonsTransfer',
    version='0.1dev',
    author='Robin Krahl',
    author_email='me@robin-krahl.de',
    packages=['simplecommonstransfer'],
    package_date={'simplecommonstransfer': ['templates/*.html']},
    license='LICENSE',
    long_description=open('README.md').read(),
    install_requires=reqs,
)

